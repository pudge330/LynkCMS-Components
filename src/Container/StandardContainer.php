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
 * @subpackage Container
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Container;

use Closure;
use Symfony\Component\Yaml\Yaml;

class StandardContainer {

	/**
	 * @var array The data array
	 */
	protected $data;

	/**
	 * @param array|object|StandardContainer $data Optional. Data array to store
	 */
	public function __construct($data = []) {
		$this->data = [];
		$this->merge($data);
	}

	/**
	 * PHP magic get method to get a value by key using the object property syntax.
	 * 
	 * @param string $key The values key
	 * 
	 * @return mixed The value or null if not set
	 */
	public function __get(string $key) {
		return $this->get($key);
	}

	/**
	 * PHP magic method to set a value to a key using the object property syntax.
	 * 
	 * @param string $key The key for the value
	 * @param mixed $value The value to store
	 */
	public function __set(string $key, $value) {
		$this->set($key, $value);
	}

	/**
	 * PHP magic method to call a method stored in the global array.
	 * 
	 * @param string $method The methods key name
	 * @param array $args Optional. The parameters to call the method with as an array
	 * 
	 * @return mixed The returned value from the closure call
	 */
	public function __call(string $method, $args) {
		if ($this->has($method)) {
			$func = $this->get($method);
			return call_user_func_array($func, $args);
		}
		else
			return null;
	}

	/**
	 * Get value.
	 * 
	 * @param string $key The values key
	 * 
	 * @return mixed The stored value or null if non-existent
	 */
	public function get(string $key) {
		return $this->has($key) ? $this->data[$key] : null;
	}

	/**
	 * Set value by key.
	 * 
	 * @param string $key The values key
	 * @param mixed $value The value to store
	 */
	public function set(string $key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * Merge data into existing array.
	 * 
	 * @param array|object|StandardContainer $data The data to merge
	 * @param bool $after Merge data into existing array
	 */
	public function merge($data, $after = false) {
		if ($data instanceof StandardContainer) {
			$data = $data->export();
		}
		if (is_object($data)) {
			$array = [];
			foreach ($data as $key => $value) {
				$array[$key] = $value;
			}
			$data = $array;
		}
		$this->data = $after ? array_merge($this->data, $data) : array_merge($data, $this->data);
	}

	/**
	 * Check whether or not a key exists.
	 * 
	 * @param string $key The key to check for
	 * 
	 * @return bool True if key exists, false if not
	 */
	public function has(string $key) {
		return array_key_exists($key, $this->data);
	}

	/**
	 *  Remove a value.
	 * 
	 * @param string|array $key The key of the value to remove
	 */
	public function remove($key) {
		$key = is_array($key) ? $key : [$key];
		foreach ($key as $k) {
			unset($this->data[$k]);
		}
	}

	/**
	 * Clear data in array.
	 */
	public function clear() {
		$this->data = [];
	}

	/**
	 * Remove all data in array.
	 * 
	 * @deprecated #1819a39 Use clear()
	 */
	public function removeAll() {
		$this->clear();
	}

	/**
	 * Check if value is null.
	 * 
	 * @param string $key The values key to ckeck
	 * 
	 * @return bool true if value is null or non-existent, false otherwise
	 */
	public function isNull(string $key) {
		return $this->has($key) ? is_null($this->get($key)) : true;
	}

	/**
	 * Check if value is empty.
	 * 
	 * @param string $key The values key to check
	 * 
	 * @return bool True id value is null, empty or and empty string, false otherwise
	 */
	public function isEmpty(string $key) {
		if ($this->has($key)) {
			$value = $this->get($key);
			return (is_null($value) || empty($value) || $value == '');
		}
		else
			return true;
	}

	/**
	 * Check if a value is a closure or not.
	 * 
	 * @param string $key The values key
	 * 
	 * @return bool True if closure, false if not
	 */
	public function isClosure(string $key) {
		if ($this->has($key)) {
			return ($this->get($key) instanceof Closure);
		}
		return false;
	}

	/**
	 * Get the data type of a stored value.
	 * 
	 * @param string $key The key of the value
	 * 
	 * @return string The data type as a string
	 */
	public function type(string $key) {
		if ($this->has($key)) {
			return $this->isClosure($key) ? 'closure' : gettype($this->get($key));
		}
		else
			return 'null';
	}

	/**
	 * Run callback for each value in array. Passing in current index, key and value as parameters.
	 * 
	 * @param Closure $closure Callback closure
	 */
	public function each(Closure $callback) {
		$index = -1;
		foreach ($this->data as $key => $value) {
			$index++;
			$callback($index, $key, $value);
		}
	}

	/**
	 * Export/return the array of data.
	 * 
	 * @return array The array of data
	 */
	public function export() {
		return $this->data;
	}

	/**
	 * Export/return the array of data.
	 * Here for backwards compatibility.
	 * 
	 * @return array The array of data
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Export/return an array of data keys.
	 * 
	 * @return array The array of keys
	 */
	public function exportKeys() {
		return array_keys($this->data);
	}

	/**
	 * Export the data, without closures to JSON format.
	 * 
	 * @return string The array in JSON form
	 */
	public function toJSON() {
		if (version_compare(phpversion(), '5.4', '<'))
			return json_encode($this->getDataWithoutClosures());
		else //--JSON_PRETTY_PRINT is available
			return json_encode($this->getDataWithoutClosures(), JSON_PRETTY_PRINT);
	}

	/**
	 * Export the data, without closures to PHP serialized format.
	 * 
	 * @return string The array in PHP serialized form
	 */
	public function toSerialize() {
		return serialize($this->getDataWithoutClosures());
	}

	/**
	 * Export the data, without closures to YAML format.
	 * 
	 * @return string The array in YAML form
	 */
	public function toYAML() {
		return Yaml::dump($this->getDataWithoutClosures(), 3);
	}

	/**
	 * Export data without closures.
	 * 
	 * @param StandardContainer $obj Optional. Standard container to export data from
	 * 
	 * @return array Data without closures
	 */
	public function getDataWithoutClosures(StandardContainer $obj = null) {
		$obj = $obj ? $obj->export() : $this->export();
		$arr = [];
		foreach ($obj as $key => $val) {
			if (!($val instanceof Closure)) {
				$arr[$key] = $val;
			}
		}
		return $arr;
	}

	/**
	 * Convert an array of keys and data to an array of StandardContainers.
	 * 
	 * @param array $a An array of key array data pairs
	 * 
	 * @return array The array of StandardContainers
	 */
	public static function convertArrays(array $a) {
		foreach ($a as $akey => &$aval) {
			$aval = static::convertArray($aval);
		}
		return $a;
	}

	/**
	 * Convert an array to a StandardContainer.
	 * 
	 * @return StandardContainer A new instance of StandardContainer
	 */
	public static function convertArray(array $arr) {
		return new StandardContainer($arr);
	}
}