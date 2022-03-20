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
 * @subpackage Connection
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Connection;

use Exception;
use PDO;

/**
 * Connection class that wraps the standard PDO object to add
 * a little bit of custom functionality and also allow the
 * use of the native PDO api via the __call magic method.
 */
class ConnectionWrapped {

	/**
	 * @var \PDO The underlying PDO object.
	 */
	protected $pdo;

	/**
	 * @param \PDO $pdo A PDO database object.
	 */
	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	/**
	 * @param string $name The method name.
	 * @param Array $args The method arguments.
	 */
	public function __call($name, $args) {
		if (method_exists($this->pdo, $name)) {
			return call_user_func_array(Array($this->pdo, $name), $args);
		}
		else {
			throw new Exception("Method {$name} not found on Connection or the underlying PDO object");
		}
	}

	/**
	 * Returns the underlying PDO instance.
	 *
	 * @return \PDO The PDO object, in this case the current instance.
	 */
	public function pdo() {
		return $this->pdo;
	}

	/**
	 * Get the PDO driver name.
	 *
	 * @return string The pdo driver name.
	 */
	public function getDriver() {
		return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	}

	/**
	 * Quote a string for use directly in a query or quote an array of strings
	 * and concatenate them together for direct use in a query.
	 *
	 * @param string|Array $string The string or array of strings to be quoted.
	 * @param int $parameterType Provides a data type hint for drivers that have alternate quoting styles. (https://www.php.net/manual/en/pdo.quote.php)
	 * 
	 * @return string The quoted string or concatenated array of strings.
	 */
	public function quote($string, $parameterType = PDO::PARAM_STR) {
		return Connection::quoteString($this->pdo, $string, $parameterType);
	}

	/**
	 * Run a SQL query.
	 *
	 * @param string $query The query to run.
	 * @param Array $parameters Optional. An array of parameters to bind to the query.
	 * @param bool $delayFetch Optional. Whether or not to delay the data fetching, useful for large datasets.
	 * 
	 * @return Array The SQL result as an array.
	 */
	public function run($query, $parameters = null, $delayFetch = false) {
		return Connection::runQuery($this->pdo, $query, $parameters, $delayFetch);
	}
}