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
 * Connection class that extends the standard PDO object
 * to add a little bit of custom functionality.
 */
class Connection extends PDO {

	/**
	 * @var bool Whether or not the PDO driver supports the lastInsertId() method.
	 */
	protected static $__supportsLastInsertId = null;

	/**
	 * Returns the PDO instance. This is only included so that the
	 * Connection and ConnectionWrapped class has the same interface.
	 *
	 * @return \PDO The PDO object, in this case the current instance.
	 */
	public function pdo() {
		return $this;
	}

	/**
	 * Get the PDO driver name.
	 *
	 * @return string The pdo driver name.
	 */
	public function getDriver() {
		return $this->getAttribute(PDO::ATTR_DRIVER_NAME);
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
		return self::quoteString($this, $string, $parameterType);
	}

	/**
	 * Quote a string for use directly in a query or quote an array of strings
	 * and concatenate them together for direct use in a query.
	 *
	 * @param \PDO $pdo A PDO object.
	 * @param string|Array $string The string or array of strings to be quoted.
	 * @param int $parameterType Provides a data type hint for drivers that have alternate quoting styles. (https://www.php.net/manual/en/pdo.quote.php)
	 * 
	 * @return string The quoted string or concatenated array of strings.
	 */
	public static function quoteString(PDO $pdo, $string, $parameterType = PDO::PARAM_STR) {
		if (is_array($string)) {
			$str = '';
			foreach ($string as $s) {
				$str .= ($str != '' ? ',' : '') . parent::quote($s, $parameterType);
			}
			return $str;
		}
		else {
			return parent::quote($string, $parameterType);
		}
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
		return self::runQuery($this, $query, $parameters, $delayFetch);
	}

	/**
	 * Run a SQL query.
	 *
	 * @param \PDO $pdo A PDO object.
	 * @param string $query The query to run.
	 * @param Array $parameters Optional. An array of parameters to bind to the query.
	 * @param bool $delayFetch Optional. Whether or not to delay the data fetching, useful for large datasets.
	 * 
	 * @return Array The SQL result as an array.
	 */
	public static function runQuery(PDO $pdo, $query, $parameters = null, $delayFetch = false) {
		$result = Array(
			'result' => false
			,'rowCount' => 0
			,'data' => []
			,'insertId' => null
			,'statement' => null
			,'exception' => null
			,'errorMessage' => null
			,'errorCode' => null
		);
		try {
			$query = trim($query);
			$queryToLower = trim(strtolower($query));
			$isSelect = preg_match('/^(\(|\s)*select/', $queryToLower);
			$isInsert = substr($queryToLower, 0, 6) === 'insert';
			$stmt = null;
			if (is_array($parameters)) {
				foreach ($parameters as $key => $value) {
					$regex = '/\(\s*:' . preg_quote($key) . '\s*\)/';
					if (!is_numeric($key) && is_array($value) && preg_match($regex, $query)) {
						$str = '';
						$count = -1;
						foreach ($value as $v) {
							$count++;
							$str .= ($str != '' ? ',' : '') . ":{$key}_{$count}";
							$parameters[":{$key}_{$count}"] = $v;
						}
						unset($parameters[$key]);
						$query = preg_replace($regex, "($str)", $query);
					}
				}
				$stmt = $pdo->prepare($query);
				$stmtResult = $stmt->execute($parameters);
				$result['statement'] = $stmt;
				if ($stmtResult) {
					$result['result'] = true;
				}
			}
			else {
				$stmt = $pdo->query($query);
				$result['statement'] = $stmt;
				if ($stmt) {
					$result['result'] = true;
				}
			}
			if ($result['result']) {
				if ($isSelect) {
					$result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$result['rowCount'] = sizeof($result['data']);
				}
				else if ($isInsert) {
					if (self::$__supportsLastInsertId === null) {
						try {
							$result['insertId'] = $pdo->lastInsertId();
							self::$__supportsLastInsertId = true;
						}
						catch (Exception $e) {
							self::$__supportsLastInsertId = false;
						}
					}
					else if (self::$__supportsLastInsertId) {
						$result['insertId'] = $pdo->lastInsertId();
					}
				}
				if (!$isSelect) {
					$r['rowCount'] = $stmt->rowCount();
				}
			}
			return $result;
		}
		catch (Exception $exception) {
			$result['exception'] = $exception;
			$result['errorMessage'] = $exception->getMessage();
			$result['errorCode'] = $exception->getCode();
			error_log("Query Error [{$exception->getCode()}]: {$query} : {$exception->getMessage()}");
			return $result;
		}
	}
}