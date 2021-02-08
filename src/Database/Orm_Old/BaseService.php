<?php
namespace BGStudios\Component\Database\Orm;

use Exception;
use PDO;
use BGStudios\Component\Connection\Connection;
use BGStudios\Component\Connection\ConnectionWrap;
use BGStudios\Component\Database\AbstractEntity;
use BGStudios\Component\Database\DatabaseService;
use BGStudios\Component\Database\QueryBuilder;
use Symfony\Component\Yaml\Yaml;

class BaseService {
	protected $connection;
	protected $queryBuilder;
	protected $dbService;
	public function __construct($connection, $persistStrategy = '') {
		if (!($connection instanceof PDO || $connection instanceof ConnectionWrap)) {
			throw new Exception('BaseService: First argument of constructor must be instance of PDO or ConnectionWrap.');
		}
		$this->connection = $connection instanceof Connection || $connection instanceof ConnectionWrap
			? $connection
			: new ConnectionWrap($connection);
		$this->driver = $this->connection->getDriver();
		$this->queryBuilder = new QueryBuilder($this->connection);
		$this->dbService = new DatabaseService($this->connection);
	}
	public function getSourceConnection() {
		return $this->connection;
	}
	public function find($criteria = Array(), $mod = Array()) {
		return $this->dbService->find($this->table, $criteria, $mod);
	}
	public function findOne($criteria = Array(), $mod = Array()) {
		$mod['limit'] = 1;
		return $this->find($criteria, $mod);
	}
	public function findScalar($criteria = Array(), $mod = Array()) {
		$mod['scalar'] = true;
		return $this->find($criteria, $mod);
	}
	public function findOneScalar($criteria = Array(), $mod = Array()) {
		$mod['limit'] = 1;
		$mod['scalar'] = true;
		return $this->find($criteria, $mod);
	}
	public function __call($name, $arguments) {
		preg_match('/^(findBy|findOneBy|findScalarBy|findOneScalarBy)(\w+)$/', $name, $match);
		if (sizeof($match) > 0) {
			$action = $match[1];
			$field = $match[2];
			$columnMap = $this->entityColumnMap();
			if (isset($columnMap[$field])) {
				$criteria = Array('select' => 'this.*');
				$criteria[$columnMap[$field]] = $arguments[0];
				$action = preg_replace('/By$/', '', $action);
				return $this->{$action}($criteria);
			}
			else
				throw new Exception("findBy* - No entity property named '{$field}' in " . $this->entityName);
		}
		throw new Exception("Method {$name}() does not exist in " . get_called_class());
	}
	protected function verifyKeys($data) {
		$keyExist = true;
		$keyValues = Array();
		$keys = is_array($this->primaryKey) ? $this->primaryKey : Array($this->primaryKey);
		foreach ($keys as $key) {
			if (!isset($data[$key])) {
				$keyExist = false;
				continue;
			}
			else {
				$keyValues[$key] = $data[$key];
			}
		}
		return Array($keyExist, $keyValues);
	}
	public function persist(AbstractEntity $entity) {
		$data = $entity->exportScalarMapped();
		list($keyExist, $keyValues) = $this->verifyKeys($data);
		if ($keyExist) {
			$keyValues['select'] = 'COUNT(*)';
			$existingEntity = $this->find($keyValues, Array('limit' => 1));
			if ((int)$existingEntity > 0) {
				return $this->update($entity);
			}
			else {
				return $this->insert($entity);
			}
		}
		return $this->insert($entity);
	}
	public function insert(AbstractEntity $entity) {
		$data = $entity->exportScalarMapped();
		$insertId = $this->dbService->insert($this->table, Array(
			'columns' => implode(',', array_keys($data))
			,'values' => $data
		));
		if ($insertId) {
			$entity->{'set' . ucfirst($this->primaryKey)}($insertId);
		}
		return $insertId;
	}
	public function update(AbstractEntity $entity) {
		$keys = is_array($this->primaryKey) ? $this->primaryKey : Array($this->primaryKey);
		$data = $entity->exportScalarMapped();
		list($keyExist, $updateCriteria) = $this->verifyKeys($data);
		$updateCriteria['set'] = $data;
		if ($keyExist)
			return $this->dbService->update($this->table, $updateCriteria);
		else
			return $this->insert($entity);
	}
	public function delete(AbstractEntity $entity) {
		$keys = is_array($this->primaryKey) ? $this->primaryKey : Array($this->primaryKey);
		$data = $entity->exportScalarMapped();
		list($keyExist, $deleteCriteria) = $this->verifyKeys($data);
		if ($keyExist)
			return $this->dbService->delete($this->table, $deleteCriteria);
		else {
			//--throw exception, cannot delete entity without primary key
		}
	}
	protected function needsJoin($needle) {
		$args = func_get_args();
		array_shift($args);
		foreach ($args as $arg) {
			$arg = is_array($arg) ? implode('::G::', $arg) : $arg;
			if (strpos($arg, $needle) !== false) {
				return true;
			}
		}
		return false;
	}
	protected function toCamelCase($string) {
		$parts = explode('_', $string);
		$string = array_shift($parts);
		while (sizeof($parts)) {
			$string .= ucfirst($parts[0]);
			array_shift($parts);
		}
		return $string;
	}
	protected function toProperCase($string) {
		return ucfirst($this->toCamelCase($string));
	}
}