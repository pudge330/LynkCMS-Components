<?php
namespace LynkCMS\Component\Migration;

use LynkCMS\Component\Connection\ConnectionWrapped;

class MigrationQuery {
	protected $query;
	protected $parameters;
	protected $name;
	public function __construct($query, $parameters, $name = null) {
		$this->query = $query;
		$this->parameters = is_array($parameters) ? $parameters : Array();
		$this->name = $name ?: md5(microtime(true) . $this->query . sizeof($this->parameters));
	}
	public function getQuery() {
		return $this->query;
	}
	public function setQuery($query) {
		$this->query = $query;
	}
	public function getParameters() {
		return $this->parameters;
	}
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}
	public function getParameter($key) {
		if (array_key_exists($key, $this->parameters))
			return $this->parameters[$key];
	}
	public function setParameter($key, $value) {
		$this->parameters[$key] = $value;
	}
	public function getName() {
		return $this->name;
	}
	public function run(ConnectionWrapped $connection) {
		$result = $connection->run($this->query, $this->parameters);
		return $result['result'];
	}
}