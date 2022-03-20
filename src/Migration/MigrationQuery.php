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

use Lynk\Component\Connection\ConnectionWrapped;

/**
 * Migration query.
 */
class MigrationQuery {

	/**
	 * @var string Query to run.
	 */
	protected $query;

	/**
	 * @var Array Query parameters.
	 */
	protected $parameters;

	/**
	 * @var string Query name.
	 */
	protected $name;

	/**
	 * @param string $query Query to run.
	 * @param Array $parameters Query parameters.
	 * @param string $name Optional. Query name.
	 */
	public function __construct($query, $parameters, $name = null) {
		$this->query = $query;
		$this->parameters = is_array($parameters) ? $parameters : Array();
		$this->name = $name ?: md5(microtime(true) . $this->query . sizeof($this->parameters));
	}

	/**
	 * Get query.
	 * 
	 * @return string The query.
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Set query.
	 * 
	 * @param string $query Query to run.
	 */
	public function setQuery($query) {
		$this->query = $query;
	}

	/**
	 * Get query parameters.
	 * 
	 * @return Array Query parameters.
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Set query parameters.
	 * 
	 * @param Array $parameters Query parameters.
	 */
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}

	/**
	 * Get query parameter.
	 * 
	 * @param string $key Parameter key.
	 * 
	 * @return mixed Query parameter.
	 */
	public function getParameter($key) {
		if (array_key_exists($key, $this->parameters))
			return $this->parameters[$key];
	}

	/**
	 * Set query parameter.
	 * 
	 * @param string $key Parameter key.
	 * @param mixed $value Parameter value.
	 */
	public function setParameter($key, $value) {
		$this->parameters[$key] = $value;
	}

	/**
	 * Get query name.
	 * 
	 * @return string Query name.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Run query.
	 * 
	 * @param ConnectionWrapped $connection Database connection.
	 * 
	 * @return bool True if successful, false if not.
	 */
	public function run(ConnectionWrapped $connection) {
		$result = $connection->run($this->query, $this->parameters);
		return $result['result'];
	}
}