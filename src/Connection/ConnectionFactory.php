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
 * @subpackage Connection
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Connection;

use Closure;
use Exception;

/**
 * Class the provides ability to create a new Connection class.
 */
class ConnectionFactory {

	/**
	 * @var Closure Callback that handles logging errors.
	 */
	protected static $logger = null;

	/**
	 * @var int Last error number.
	 */
	protected static $errNo = null;

	/**
	 * @var string Last error message.
	 */
	protected static $errMsg = null;

	/**
	 * @var Array The array of connection settings.
	 */
	protected static $errSettings = null;

	// const SQLITE = 0;
	// const MYSQL = 1;

	/**
	 * Set the logger callback.
	 *
	 * @param Closure A closure that logs PDO errors upon creation.
	 */
	public static function setLogger(Closure $logger) {
    	static::$logger = $logger;
    }

    /**
     * Get a new connection constructed from an array of connection settings.
     *
     * @param Array $s The connection settings.
     * @param string $root Optional. The root directory for SQLite connections with relative paths.
     * 
     * @return Connection The new Connection object.
     */
	public static function get($s, $root = null) {
		if ($root) {
			$root = rtrim($root, '/') . '/';
		}
		try {
			$args = Array('dsn' => '', 'username' => '', 'password' => '');
			$args['username'] = isset($s['username']) ? $s['username'] : '';
			$args['password'] = isset($s['password']) ? $s['password'] : '';
			if (is_string($s)) {
				$args['dsn'] = $s;
			}
			else if (is_array($s)) {
				if (isset($s['dsn'])) {
					$args['dsn'] = $s['dsn'];
				}
				else {
					switch ($s['driver']) {
						case 'mysql':
						case 'pdo_mysql':
							$charset = isset($s['charset']) ? ";charset={$s['charset']}" : '';
							$port = isset($s['port']) ? ";port={$s['port']}" : '';
							$dsn = "mysql:host={$s['host']};dbname={$s['db']}{$charset}{$port}";
							$args['dsn'] = $dsn;
							break;
						case 'sqlite':
						case 'pdo_sqlite':
							$prefix = DIRECTORY_SEPARATOR == '/' ? 'sqlite:' . DIRECTORY_SEPARATOR : 'sqlite:';
							$args['dsn'] = $prefix  . $root . $s['path'];
							break;
					}
				}	
			}
			$options = [Connection::ATTR_EMULATE_PREPARES => false, Connection::ATTR_ERRMODE => Connection::ERRMODE_EXCEPTION];
			if (in_array($s['driver'], ['mysql', 'pdo_mysql'])) {
				$options[Connection::MYSQL_ATTR_FOUND_ROWS] = true;
			}
			return new Connection(
				$args['dsn'], $args['username'], $args['password'],
				$options
			);
		}
		catch (Exception $ex) {
			static::$errNo = $ex->getCode();
			static::$errMsg = $ex->getMessage();
			static::$errSettings = $s;
			foreach ($s as $k => $v) {
				if ($k == 'password')
					$s[$k] = str_repeat('*', strlen($v));
			}
			if (static::$logger && static::$logger instanceof Closure)
            	static::$logger('ConnectionFactory Error - ' . static::$errMsg, ['settings' => $s, 'errno' => static::$errNo, 'errmsg' => static::$errMsg]);
		}
		return null;
	}

	/**
	 * Get the last connection error information.
	 *
	 * @return Array An array of connection error information including the error number, error message and error connection settings.
	 */
	public static function error() {
		$d = ['no' => static::$errNo, 'msg' => static::$errMsg, 'settings' => static::$errSettings];
		static::$errNo = null;
		static::$errMsg = null;
		static::$errSettings = null;
		return $d;
	}
}