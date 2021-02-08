<?php
/**
 * This file is part of the BGStudios Connection Component.
 *
 * (c) Brandon Garcia <brandon@bgstudios.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package BGStudios PHP Components
 * @subpackage Connection
 * @author Brandon Garcia <brandon@bgstudios.io>
 */

namespace BGStudios\Component\Connection;

use Closure;
use Exception;
use PDO;

/**
 * Connection pool manager class that managing multiple PDO connections.
 */
class ConnectionPool {

	/**
	 * @var Array An array of connection objects.
	 */
	protected $connections;

	/**
	 * @var Array An array of connection configuration for lazy loading connections.
	 */
	protected $connectionConfig;

	/**
	 * @var string Root directory path for relative SQLite databases paths.
	 */
	protected $root;

	/**
	 * @var string The default connection object name or key.
	 */
	protected $default;

	/**
	 * @param Array $connections Optional. An array of connections objects or connection configurations.
	 * @param string $default Optional. The default connection key, defaults to 'default'.
	 * @param string $root Optional. The root directory for SQLite connections derived from configuration.
	 */
	public function __construct(Array $connections = Array(), $default = 'default', $root = null) {
		$this->connections = $this->connectionConfig = Array();
		$default = $default !== null ? $default : 'default';
		$this->setDefault($default);
		$this->root = $root;
		$this->set($connections);
	}

	/**
	 * Sets the default connection name.
	 *
	 * @param string $default The default connection name.
	 */
	public function setDefault($default) {
		if (is_string($default)) {
			$this->default = $default;
		}
		else if ($default) {
			$this->default = 'default';
			$this->set('default', $default);
		}
	}

	/**
	 * Gets the default connection name.
	 *
	 * @return string The default connection name.
	 */
	public function getDefault() {
		return $this->default;
	}

	/**
	 * Get a connection object.
	 *
	 * @param string $default Optional. The connection name to retrieve.
	 * @return Connection|ConnectionWrapped The Conenction object or null if not available.
	 */
	public function get($name = null) {
		if (!$name && $this->default)
			$name = $this->default;
		if ($this->has($name)) {
			if ($this->connections[$name] instanceof Closure) {
				$connectionFn = $this->connections[$name];
				return $connectionFn();
			}
			return $this->connections[$name];
		}
		return null;
	}

	/**
	 * Set a connection.
	 *
	 * @param string $name The name of the connection. Can also be an array of connection name and object pairs.
	 * @param \PDO|Connection|ConnectionWrapped|Array Optional. An connection object or array of configuration for lazy loading. Ignored when first parameter is an array.
	 */
	public function set($name, $connection = null) {
		if (is_array($name)) {
			foreach ($name as $n => $c) {
				$this->set($n, $c);
			}
		}
		else {
			if ($connection instanceof Connection) {
				$this->connections[$name] = $connection;
			}
			else if ($connection instanceof ConnectionWrapped) {
				$this->connections[$name] = $connection;
			}
			else if ($connection instanceof PDO) {
				$this->connections[$name] = new ConnectionWrapped($connection);
			}
			else if (is_array($connection)) {
				$t = $this;
				$this->connections[$name] = function() use ($name, $t) {
					$t->connections[$name] = NewConnection::get($t->connectionConfig[$name], $t->root);
					return $t->connections[$name];
				};
				$this->connectionConfig[$name] = $connection;
			}
		}
	}

	/**
	 * Whether or not a connection exists in the connection pool.
	 *
	 * @param string Optional. The name of the connection.
	 * @return bool true if the connection exists, false otherwise.
	 */
	public function has($name = null) {
		$name = $name === null ? $this->default : $name;
		return array_key_exists($name, $this->connections);
	}

	/**
	 * Remove a connection from the pool.
	 *
	 * @param string $name Name of the connection to remove.
	 */
	public function remove($name) {
		if ($this->has($name)) {
			unset($this->connections[$name], $this->connectionConfig[$name]);
		}
	}
}