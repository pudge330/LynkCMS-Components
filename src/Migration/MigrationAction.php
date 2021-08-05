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
 * @subpackage Migration
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Migration;

use Closure;
use LynkCMS\Component\Connection\ConnectionWrapped;

/**
 * Migration action.
 */
class MigrationAction {

	/**
	 * @var Closure Function to call.
	 */
	protected $action;

	/**
	 * @var Array Function context.
	 */
	protected $context;

	/**
	 * @var string Action name.
	 */
	protected $name;

	/**
	 * @param Closure $action Function to call.
	 * @param Array Function context.
	 * @param string $name Optional. Action name.
	 */
	public function __construct(Closure $action, $context, $name = null) {
		$this->action = $action;
		$this->context = is_array($context) ? $context : Array();
		$this->name = $name ?: md5(rand() . microtime(true) . sizeof($this->context) . rand());
	}

	/**
	 * Get action.
	 * 
	 * @return Closure The function.
	 */
	public function getAction() {
		return $this->query;
	}

	/**
	 * Set action.
	 * 
	 * @param Closure $action Function to call.
	 */
	public function setAction(Closure $action) {
		$this->query = $query;
	}

	/**
	 * Get function context.
	 * 
	 * @return Array Function context.
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * Set function context.
	 * 
	 * @param Array $context Function context.
	 */
	public function setContext($context) {
		$this->context = $context;
	}

	/**
	 * Get context item.
	 * 
	 * @param string $key Context item key.
	 * 
	 * @return mixed Context item value.
	 */
	public function getContextItem($key) {
		if (array_key_exists($key, $this->context))
			return $this->context[$key];
	}

	/**
	 * Set context item.
	 * 
	 * @param string $key Context item key.
	 * @param mixed $value Context item value.
	 */
	public function setContextItem($key, $value) {
		$this->context[$key] = $value;
	}

	/**
	 * Get action name.
	 * 
	 * @return string Action name.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Run action.
	 * 
	 * @param ConnectionWrapped $connection Database connection.
	 * @param string $root Project root directory.
	 * 
	 * @return bool True if successful, false if not.
	 */
	public function run(ConnectionWrapped $connection, $root) {
		$action = $this->action;
		$result = $action($context, $root, $connection);
		return $result;
	}
}