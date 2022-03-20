<?php
/**
 * This file is part of the Lynk Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Lynk Components
 * @subpackage Migration
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Migration;

use Closure;
use Lynk\Component\Connection\ConnectionWrapped;

/**
 * Base migration class.
 */
class Migration {

	/**
	 * @var ConnectionWrapped Database connection opbject.
	 */
	private $connection;

	/**
	 * @var string Project root directory.
	 */
	private $root;

	/**
	 * @var Array Query and action migration stack.
	 */
	private $stack;

	/**
	 * @var int Query migration count.
	 */
	private $queryCount;

	/**
	 * @var int Action migration count.
	 */
	private $actionCount;

	/**
	 * @param ConnectionWrapped $connection Database connection.
	 * @param string $root Project root directory.
	 */
	public function __construct(ConnectionWrapped $connection, $root) {
		$this->connection = $connection;
		$this->root = $root;
		$this->stack = Array();
		$this->queryCount = $this->actionCount = 0;
	}

	/**
	 * Up migration.
	 */
	public function up() {
		throw new Exception('Migration Error: function up() must be implemented in the migration ' . get_class());
	}

	/**
	 * Down migration.
	 */
	public function down() {
		throw new Exception('Migration Error: function down() must be implemented in the migration ' . get_class());
	}

	/**
	 * Get database connection.
	 * 
	 * @return ConnectionWrapped Database connection opbject.
	 */
	protected function getConnection() {
		return $this->connection;
	}

	/**
	 * Get project root.
	 * 
	 * @return string Project root.
	 */
	protected function getRoot() {
		return $this->root;
	}

	/**
	 * Do up migration. Only adds migrations to stack.
	 * 
	 * @return Migration This instance.
	 */
	public function doUp() {
		$this->stack = Array();
		$this->queryCount = $this->actionCount = 0;
		$this->up();
		return $this;
	}

	/**
	 * Do down migration. Only adds migrations to stack.
	 * 
	 * @return Migration This instance.
	 */
	public function doDown() {
		$this->stack = Array();
		$this->queryCount = $this->actionCount = 0;
		$this->down();
		return $this;
	}

	/**
	 * Add query to migration stack.
	 * 
	 * @param mixed $query MigrationQuery or query to run.
	 * @param Array $parameters Optional. Query parameters.
	 * @param string $name Optional. Name of query.
	 */
	protected function addQuery($query, $parameters = Array(), $name = null) {
		if (!($query instanceof MigrationQuery))
			$query = new MigrationQuery($query, $parameters, $name);
		$this->stack[] = $query;
		$this->queryCount++;
	}

	/**
	 * Add action to migration stack.
	 * 
	 * @param mixed $action MigrationAction or closure to run.
	 * @param Array $context Optional. Context for action.
	 * @param string $name Optional. Name of action.
	 */
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

	/**
	 * Run migration stack.
	 * 
	 * @param Closure Optional. Callback function.
	 * 
	 * @return Array Migration success and failure results.
	 */
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

	/**
	 * Get full stack count.
	 * 
	 * @return int Stack count.
	 */
	public function getStackCount() {
		return $this->queryCount + $this->actionCount;
	}

	/**
	 * Get query migration count.
	 * 
	 * @return int Query count.
	 */
	public function getQueryCount() {
		return $this->queryCount;
	}

	/**
	 * Get action migration count.
	 * 
	 * @return int Action count.
	 */
	public function getActionCount() {
		return $this->actionCount;
	}

	/**
	 * Get migration version.
	 * 
	 * @return string Migration version.
	 */
	public function getVersion() {
		$classname = get_class($this);
		if ($pos = strrpos($classname, '\\')) $classname = substr($classname, $pos + 1);
	    if (preg_match('/^Migration(.+)/', $classname, $match)) {
	    	return $match[1];
	    }
	    return $classname;
	}
}