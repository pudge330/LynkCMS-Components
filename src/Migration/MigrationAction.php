<?php
namespace BGStudios\Component\Migration;

use Closure;
use BGStudios\Component\Connection\ConnectionWrapped;

class MigrationAction {
	protected $action;
	protected $context;
	protected $name;
	public function __construct(Closure $action, $context, $name = null) {
		$this->action = $action;
		$this->context = is_array($context) ? $context : Array();
		$this->name = $name ?: md5(rand() . microtime(true) . sizeof($this->context) . rand());
	}
	public function getAction() {
		return $this->query;
	}
	public function setAction(Closure $action) {
		$this->query = $query;
	}
	public function getContext() {
		return $this->context;
	}
	public function setContext($context) {
		$this->context = $context;
	}
	public function getContextItem($key) {
		if (array_key_exists($key, $this->context))
			return $this->context[$key];
	}
	public function setContextItem($key, $value) {
		$this->context[$key] = $value;
	}
	public function getName() {
		return $this->name;
	}
	public function run(ConnectionWrapped $connection, $root) {
		$action = $this->action;
		$result = $action($context, $root, $connection);
		return $result;
	}
}