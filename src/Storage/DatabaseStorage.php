<?php
namespace BGstudios\Component\Storage;

use Exception;
use PDO;
use BGStudios\Component\Connection;

class DatabaseStorage implements StorageInterface {
	protected $connection;
	protected $table;
	protected $columns;
	public function __construct($connection = null, $schema = null) {
		if (!$connection || (!($connection instanceof PDO) && !($connection instanceof Connection\ConnectionWrapped))) {
			throw new Exception('BGStudios\\Component\\Storage\\DatabaseStorage requires a PDO or Connection\ConnectionWrapped object');
		}
		if (!($connection instanceof Connection\Connection) && !($connection instanceof Connection\ConnectionWrapped)) {
			$connection = new Connection\ConnectionWrapped($connection);
		}
		$this->connection = $connection;
		if ($schema === null) {
			$this->table = 'database_storage';
			$this->columns = ['identifier', 'value'];
		}
		else if (isset($schema['table']) && isset($schema['columns']) && sizeof($schema) > 1) {
			$this->table = $schema['table'];
			$this->columns = $schema['columns'];
		}
		else {
			throw new Exception('Not a valid schema');
		}
	}
	public function get($key) {
		$singleResult = false;
		if (!is_array($key)) {
			$singleResult = true;
			$key = Array($key);
		}
		$result = $this->connection->run("SELECT {$this->columns[0]},{$this->columns[1]} FROM {$this->table} WHERE {$this->columns[0]} IN(:ids)", ['ids' => $key]);
		if ($singleResult) {
			$resultValue = $result['result'] && $result['rowCount'] ? $result[0][$this->columns[1]] : null;
			return $resultValue;
		}
		$final = Array();
		foreach ($key as $k) {
			$final[$k] = null;
		}
		foreach ($result['data'] as $d) {
			if (array_key_exists($d[$this->columns[0]], $final)) {
				$final[$d[$this->columns[0]]] = $d[$this->columns[1]];
			}
		}
		return $final;
	}
	public function set($key, $value = null) {
		/*
			Wanted the ability to accept an array as the first argument that is populated with key/value pairs. Decided the most performant way of inserting multiple records at once without checking if a record exists for each one or using a mysql REPLACE query (want to keep sqlite compatible) would be to run a delete query with the identifiers first then one insert with all the data.
		*/
		if (!is_array($key)) {
			$key = [$key => $value];
		}
		$this->connection->run("DELETE FROM {$this->table} WHERE {$this->columns[0]} IN(:ids)", ['ids' => array_keys($key)]);
		$insert = '';
		$params = [];
		$count = -1;
		foreach ($key as $k => $v) {
			$count++;
			$insert .= (!$count ? '' : ',') . "(:i_{$count},:v_{$count})";
			$params[":i_{$count}"] = $k;
			$params[":v_{$count}"] = $v;
		}
		$result = $this->connection->run("INSERT INTO {$this->table}({$this->columns[0]},{$this->columns[1]}) VALUES {$insert}", $params);
		return $result['result'];
	}
	public function has($key) {
		$result = $this->connection->run("SELECT COUNT(*) FROM {$this->table} WHERE {$this->columns[0]}=:id", ['id' => $key]);
		return ($result['result'] && $result['data'][0]['COUNT(*)'] > 0);
	}
	public function remove($key) {
		$result = $this->connection->run("DELETE FROM {$this->table} WHERE {$this->columns[0]}=:id", ['id' => $key]);
		return $result['result'];
	}
}