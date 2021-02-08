<?php
namespace LynkCMS\Component\Migration;

use Closure;
use LynkCMS\Component\Connection\ConnectionWrapped;

class Migration {
	private $connection;
	private $root;
	private $stack;
	private $queryCount;
	private $actionCount;

	public function __construct(ConnectionWrapped $connection, $root) {
		$this->connection = $connection;
		$this->root = $root;
		$this->stack = Array();
		$this->queryCount = $this->actionCount = 0;
	}

	public function up() {
		throw new Exception('Migration Error: function up() must be implemented in the migration ' . get_class());
	}

	public function down() {
		throw new Exception('Migration Error: function down() must be implemented in the migration ' . get_class());
	}

	protected function getConnection() {
		return $this->connection;
	}

	protected function getRoot() {
		return $this->root;
	}

	public function doUp() {
		$this->stack = Array();
		$this->queryCount = $this->actionCount = 0;
		$this->up();
		return $this;
	}

	public function doDown() {
		$this->stack = Array();
		$this->queryCount = $this->actionCount = 0;
		$this->down();
		return $this;
	}

	protected function addQuery($query, $parameters = Array(), $name = null) {
		if (!($query instanceof MigrationQuery))
			$query = new MigrationQuery($query, $parameters, $name);
		$this->stack[] = $query;
		$this->queryCount++;
	}

	protected function addAction($action, $context = Array(), $name = null) {
		if (!($action instanceof MigrationAction) && $action instanceof Closure)
			$action = new MigrationAction($action, $context, $name);
		else if (!($action instanceof MigrationAction))
			$action = null;
		if ($action) {
			$this->stack[] = $action;
			$this->actionCount++;
		}
	}

	public function runStack($callback = null) {
		$callback = $callback ?: function() {};
		$successful = $failed = $successfulQueries = $failedQueries = $successfulActions = $failedActions = Array();
		foreach ($this->stack as $item) {
			if (sizeof($failed)) {
				continue;
			}
			if ($item instanceof MigrationQuery) {
				$result = $item->run($this->connection);
				$callback('query', $result, $item->getQuery());
				if ($result) {
					$successful[] = 'Query > ' . $item->getQuery();
					$successfulQueries[] = $item->getQuery();
				}
				else {
					$failed[] = 'Query > ' . $item->getQuery();
					$failedQueries[] = $item->getQuery();
				}
			}
			else if ($item instanceof MigrationAction) {
				$result = $item->run($this->connection, $this->root);
				$callback('action', $result, $item->getName());
				if ($result) {
					$successful[] = 'Action > ' . $item->getName();
					$successfulActions[] = $item->getName();
				}
				else {
					$failed[] = 'Action > ' . $item->getName();
					$failedActions[] = $item->getName();
				}
			}
		}
		return Array(
			'successful' => $successful,
			'successfulQueries' => $successfulQueries,
			'successfulActions' => $successfulActions,
			'failed' => $failed,
			'failedQueries' => $failedQueries,
			'failedActions' => $failedActions
		);
	}

	public function getStackCount() {
		return $this->queryCount + $this->actionCount;
	}

	public function getQueryCount() {
		return $this->queryCount;
	}

	public function getActionCount() {
		return $this->actionCount;
	}
	public function getVersion() {
		$classname = get_class($this);
		if ($pos = strrpos($classname, '\\')) $classname = substr($classname, $pos + 1);
	    if (preg_match('/^Migration(.+)/', $classname, $match)) {
	    	return $match[1];
	    }
	    return $classname;
	}
}