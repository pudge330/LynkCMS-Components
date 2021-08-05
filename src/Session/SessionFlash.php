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
 * @subpackage Session
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Session;

use LynkCMS\Component\Container\GlobalArrayContainer;
use Symfony\Component\Yaml\Yaml;

/**
 * Session flash storage. Once a variable is accessed it removes itself from storage.
 */
class SessionFlash {

	/**
	 * @var string The session data key for array.
	 */
	protected $sessionDataKey;

	/**
	 * @var GlobalArrayContainer Global session object.
	 */
	protected $session;

	/**
	 * @param string $name The name of the session cache key.
	 * @param GlobalArrayContainer Optional. Global session object.
	 */
	public function __construct($key = 'sessionFlash', GlobalArrayContainer $container = null) {
		$this->sessionDataKey = $key;
		$this->session = $container ?: new GlobalArrayContainer('_SESSION');
	}

	/**
	 * Get a value.
	 * 
	 * @param string $key The variable key.
	 * @param bool $emove Optional. Whether to remove the variable or not, defaults to true.
	 * 
	 * @return mixed The value from the session. Null if non-existent.
	 */
	public function get($key, $remove = true) {
		if ($this->has($key)) {
			$value = $this->export()[$key];
			if ($remove)
				$this->remove($key);
			return $value;
		}
		else
			return null;
	}

	/**
	 * Set a value to cache.
	 * 
	 * @param string $key The variable key.
	 * @param mixed $value The value to store.
	 */
	public function set($key, $value) {
		$flash = $this->export();
		$flash[$key] = $value;
		$this->session->set($this->sessionDataKey, $flash);
	}

	/**
	 * Check if a variable exists.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return bool Whether or not the varaible exists.
	 */
	public function has($key) {
		if (array_key_exists($key, $this->export()))
			return true;
		else
			return false;
	}

	/**
	 * Check if the variable is null.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return bool True if the variable is null, false if the variable is not null or doesn't exists.
	 */
	public function isNull($key) {
		if ($this->has($key)) {
			if (is_null($this->get($key)))
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Check if the variable is a empty value.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return bool True if the variable is empty, false if the varaible is not empty or doesn't exists.
	 */
	public function isEmpty($key) {
		if ($this->has($key)) {
			$value = $this->get($key);
			if (is_null($value) || empty($value) || $value == '')
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Get the data type of a stored value.
	 * 
	 * @param string $key The key of the value.
	 * 
	 * @return string The data type as a string.
	 */
	public function type($key) {
		if ($this->has($key)) {
			$value = $this->get($key);
			if ($value instanceof \Closure)
				return 'closure';
			return gettype($value);
		}
		else
			return 'null';
	}

	/**
	 * Remove a variable from session flash data.
	 * 
	 * @param string $key The variable key.
	 */
	public function remove($key) {
		if ($this->has($key)) {
			$flash = $this->export();
			unset($flash[$key]);
			$this->session->set($this->sessionDataKey, $flash);
		}
	}

	/**
	 * Export flash data.
	 * 
	 * @return Array The data.
	 */
	public function export() {
		return $this->session->get($this->sessionDataKey) ?: Array();
	}

	/**
	 * Export the data in JSON format.
	 * 
	 * @return string The data in JSON format.
	 */
	public function toJSON() {
		if (version_compare(phpversion(), '5.4', '<'))
			return json_encode($this->getDataWithoutClosures());
		else //--JSON_PRETTY_PRINT is available
			return json_encode($this->getDataWithoutClosures(), JSON_PRETTY_PRINT);
	}

	/**
	 * Export the data in PHP serialized format.
	 * 
	 * @return stirng The data in serialized format.
	 */
	public function toSerialize() {
		// $tmp = $this->getNewWithoutClosures();
		return serialize($this->getDataWithoutClosures());
	}

	/**
	 * Export the data in YAML format.
	 * 
	 * @return string The data in YAML format.
	 */
	public function toYAML() {
		return Yaml::dump($this->getDataWithoutClosures(), 3);
	}

	/**
	 * Export the data without closures.
	 * 
	 * @return Array The data without closures.
	 */
	public function getDataWithoutClosures($obj = null) {
		$obj = $obj ? $obj->export() : $this->export();
		$tmp = [];
		foreach ($obj as $key => $val) {
			if (!($val instanceof \Closure)) {
				$tmp[$key] = $val;
			}
		}
		return $tmp;
	}
}