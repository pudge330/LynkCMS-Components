<?php
/**
 * This file is part of the LynkCMS Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS Components
 * @subpackage Storage
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Storage;

use Exception;
use PDO;
use Lynk\Component\Connection;

/**
 * Database key value based storage.
 */
class DatabaseStorage implements StorageInterface {

	/**
	 * @var Connection|ConnectionWrapped Database connection opbject.
	 */
	protected $connection;

	/**
	 * @var string Table name.
	 */
	protected $table;

	/**
	 * @var Array Identifier and value.
	 */
	protected $columns;

	/**
	 * @param PDO|ConnectionWrapped $connection Database connection object.
	 * @param Array $schema Database schema. Table and column names (id,value).
	 * 
	 * @throws \Exception
	 */
	public function __construct($connection = null, $schema = null) {
		if (!$connection || (!($connection instanceof PDO) && !($connection instanceof Connection\ConnectionWrapped))) {
			throw new Exception('LynkCMS\\Component\\Storage\\DatabaseStorage requires a PDO or Connection\ConnectionWrapped object');
		}
		if (!($connection instanceof Connection\Connection) && !($connection instanceof Connection\ConnectionWrapped)) {
			$connection = new Connection\ConnectionWrapped($connection);
		}
		$this->connection = $connection;
		if ($schema === null) {
			$this->table = 'database_storage';
			$this->columns = ['identifier', 'value'];
		}
		else if (
			isset($schema['table']) && isset($schema['columns']) && sizeof($schema['columns']) == 2) {
			$this->table = $schema['table'];
			$this->columns = $schema['columns'];
		}
		else {
			throw new Exception('DatabaseStorage: Not a valid schema');
		}
	}

	/**
     * Get value(s).
     * 
     * @param mixed $key Either a string or array of string keys.
     * 
     * @return mixed Value or array of values.
     */
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

	/**
     * Set value(s).
     * 
     * @param mixed $key A key as a string or an array of key value pairs.
     * @param mixed $value The value to store or null if array is passsed for $key.
     * 
     * @return bool True if successful or false if not.
     */
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

	/**
     * Check whether or not a key exists.
     * 
     * @param string $key Key to check for.
     * 
     * @return bool True if key exists, false if not.
     */
	public function has($key) {
		$result = $this->connection->run("SELECT COUNT(*) FROM {$this->table} WHERE {$this->columns[0]}=:id", ['id' => $key]);
		return ($result['result'] && $result['data'][0]['COUNT(*)'] > 0);
	}

	/**
     * Remove a value from storage.
     * 
     * @param string $key The key of the value to be removed.
     * 
     * @return bool True if successfully removed the value, false if not.
     */
	public function remove($key) {
		$result = $this->connection->run("DELETE FROM {$this->table} WHERE {$this->columns[0]}=:id", ['id' => $key]);
		return $result['result'];
	}
}