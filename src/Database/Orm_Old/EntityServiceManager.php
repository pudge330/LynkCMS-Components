<?php
namespace BGStudios\Component\Database\Orm;

use Exception;
use PDO;
use BGStudios\Component\Connection\Connection;
use BGStudios\Component\Connection\ConnectionWrap;

class EntityServiceManager {
	protected $connection;
	protected $namespaceMap;
	public function __construct($connection) {
		if (!($connection instanceof PDO || $connection instanceof ConnectionWrap)) {
			throw new Exception('QueryBuilder: First argument of constructor must be instance of PDO or ConnectionWrap.');
		}
		$this->connection = $connection instanceof Connection || $connection instanceof ConnectionWrap
			? $connection
			: new ConnectionWrap($connection);
	}
	public function addEntityNamespace($key, $namespace) {
		$key = !preg_match('/^(.*)([^\w\s])$/', $key) ? "{$key}:" : '';
		$this->namespaceMap[$key] = rtrim($namespace, '\\') . '\\Service';
	}
	public function hasEntityNamespace($key) {
		return array_key_exists($key, $this->namespaceMap);
	}
	public function getEntityNamespace($key) {
		if ($this->hasEntityNamespace($key))
			return $this->namespaceMap[$key];
	}
	public function getEntityNamespaces() {
		return $this->namespaceMap;
	}
	public function getService($name) {
		if (preg_match('/^(.+[^\w\s])(\w+)$/', $name, $match)) {
			if ($this->hasEntityNamespace($match[1])) {
				$name = $this->getEntityNamespace($match[1]) . '\\' . $match[2];
			}
			else {
				throw new Exception("EntityServiceManager: No entity namespace called '{$match[1]}'.");
			}
		}
		return new $name($this->connection);
	}
}