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
 * @subpackage Database
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Database;

use DateTime;
use Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * Abstract entity class.
 */
class AbstractEntity {

	/**
	 * @var bool Is Symfony YAML supported.
	 */
	public static $isSymfonyYamlSupported = null;

	/**
	 * @var Array Temporary data array.
	 */
	protected $__tmpData = Array();

	/**
	 * @param Array $data Optional. Entity data.
	 * @param Array $tmpData Optional. Entity temporary data.
	 */
	public function __construct($data = Array(), $tmpData = Array()) {
		if (static::$isSymfonyYamlSupported === null) {
			static::$isSymfonyYamlSupported = class_exists('\Symfony\Component\Yaml\Yaml');
		}
		$data = is_array($data) ? $data : Array();
		$this->set($data);
		$tmpData = is_array($tmpData) ? $tmpData : Array();
		$this->setTmpData($tmpData);
	}

	/**
	 * Merge data from existing entity or array.
	 * 
	 * @param mixed $data Entity or array of data.
	 */
	public function merge($data) {
		$data = is_object($data) ? $data->export() : $data;
		$this->set($data);
	}

	/**
	 * PHP Magic call method.
	 * 
	 * @param string $name Method call name.
	 * @param Array $arguments Method call arguments.
	 * 
	 * @return mixed Data value or null if not set or not applicable.
	 * 
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		if (preg_match('/^(get|set|echo|print)(\w+)$/', $name, $match)) {
			$action = $match[1];
			$field = $match[2];
			$fieldExists = true;
			if (!property_exists($this, $field)) {
				$field = lcfirst($field);
				$fieldExists = false;
			}
			switch ($action) {
				case 'get':
					if ($fieldExists || property_exists($this, $field))
						return $this->{$field};
					else
						throw new Exception("Method {$name}() does not exist in " . get_called_class());
				break;
				case 'set':
					$this->{$field} = $arguments[0];
					return $this;
				case 'echo':
				case 'print':
					$call = "get{$match[2]}";
					return print call_user_func_array(Array($this, $call), $arguments);
				break;
			}
		}
		throw new Exception("Method {$name}() does not exist in " . get_called_class());
	}

	/**
	 * Get value.
	 * 
	 * @param string $field Property key.
	 * 
	 * @return mixed Property value or null if not set.
	 */
	public function get($field) {
		if (is_array($field)) {
			$values = Array();
			foreach ($field as $f) {
				$f = $this->toProperCase($f);
				$values[] = $this->{'get' . $f}();
			}
			return $values;
		}
		else if ($field) {
			$field = $this->toProperCase($field);
			return $this->{'get' . $field}();
		}
		else
			return null;
	}

	/**
	 * Set value or set of values.
	 * Either accepts an array of property key value pairs or two arguments.
	 * First being property name and second the value.
	 * 
	 * @return AbstractEntity This instance.
	 */
	public function set() {
		$args = func_get_args();
		switch (sizeof($args)) {
			case 1:
				foreach ($args[0] as $field => $value) {
					$field = $this->toProperCase($field);
					$this->{'set' . $field}($value);
				}
			break;
			case 2:
				$args[0] = $this->toProperCase($args[0]);
				$this->{'set' . $args[0]}($args[1]);
			break;
		}
		return $this;
	}

	/**
	 * Check if property exists or list of properties exist.
	 * 
	 * @param mixed $field Property name or array of property names.
	 * 
	 * @return mixed Bool, true or false if checking for single value.
	 *               Array of bools with one per property checked.
	 */
	public function has($field) {
		if (is_array($field)) {
			$return = Array();
			foreach ($field as $f)
				$return[$f] = $this->has($f);
			return $return;
		}
		else if ($field) {
			$field = $this->toCamelCase($field);
			return isset($this->$field);
		}
		else
			return null;
	}

	/**
	 * Set temporary data.
	 * Either accepts an array of temporary data key value pairs or two arguments.
	 * First being property name and second the value.
	 */
	public function setTmpData() {
		$args = func_get_args();
		switch (sizeof($args)) {
			case 1:
				foreach ($args[0] as $field => $value) {
					$this->setTmpData($field, $value);
				}
			break;
			case 2:
				$this->__tmpData[$args[0]] = $args[1];
			break;
		}
	}

	/**
	 * Get temporary data.
	 * 
	 * @param string $key Data key.
	 * 
	 * @return mixed Data value.
	 */
	public function getTmpData($key) {
		if ($this->hasTmpData($key))
			return $this->__tmpData[$key];
		else
			return null;
	}

	/**
	 * Check if temporary data exists.
	 * 
	 * @param string $key Data key.
	 * 
	 * @return bool True if exists, false otherwise.
	 */
	public function hasTmpData($key) {
		return array_key_exists($key, $this->__tmpData);
	}

	/**
	 * Remove temporary data.
	 * 
	 * @param string $key Data key.
	 */
	public function removeTmpData($key) {
		unset($this->__tmpData[$key]);
	}

	/**
	 * Export temporary data.
	 * 
	 * @return Array Temporary data.
	 */
	public function exportTmpData() {
		return $this->__tmpData;
	}

	/**
	 * Export entity data.
	 * 
	 * @return Array Entity data.
	 */
	public function export() {
		$data = (array)$this;
		unset($data["\x00*\x00__tmpData"], $data["__tmpData"]);
		return $data;
	}

	/**
	 * Create new entity and hydrate it with values.
	 * 
	 * @param Array $a Array of data.
	 * 
	 * @return AbstractEntity New entity instance.
	 */
	public static function hydrate($a) {
		foreach ($a as $akey => &$aval) {
			$aval = new static($aval);
		}
		return $a;
	}

	/**
	 * Convert string to camel case.
	 * 
	 * @param string $string String to convert.
	 * 
	 * @return string Camel cased string.
	 */
	protected function toCamelCase($string) {
		$parts = explode('_', $string);
		$string = array_shift($parts);
		while (sizeof($parts)) {
			$string .= ucfirst($parts[0]);
			array_shift($parts);
		}
		return $string;
	}

	/**
	 * Convert string to proper case.
	 * 
	 * @param string $string String to convert.
	 * 
	 * @return string Proper cased string.
	 */
	protected function toProperCase($string) {
		return ucfirst($this->toCamelCase($string));
	}

	/**
	 * Check if string is serialized data.
	 * 
	 * @param string $string The string to check.
	 * 
	 * @return bool True if string is serialized data, false otherwise.
	 */
	protected function isSerializedObject($string) {
		return \lynk\isSerialized($string);
	}

	/**
	 * Create DateTime object from timestamp.
	 * 
	 * @param string $ts Timestamp value.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed DateTime object or timestamp.
	 */
	public function toDatetimeFromTimestamp($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = new DateTime();
			$dt->setTimestamp($ts);
			return $dt;
		}
		return null;
	}

	/**
	 * Create timestamp from DateTime object.
	 * 
	 * @param DateTime $dt DateTime object.
	 * 
	 * @return int Timestamp in seconds.
	 */
	public function fromDatetimeToTimestamp($dt) {
		if ($dt instanceof DateTime) {
			return $dt->getTimestamp();
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}

	/**
	 * Create DateTime object from timestamp (YmdHis).
	 * 
	 * @param string $ts Timestamp value.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed DateTime object or timestamp.
	 */
	public function toDatetimeFromYmdHis($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = new DateTime($ts);
			return $dt;
		}
		return null;
	}

	/**
	 * Create timestamp (YmdHis) from DateTime object.
	 * 
	 * @param DateTime $dt DateTime object.
	 * 
	 * @return int Timestamp in seconds.
	 */
	public function fromDatetimeToYmdHis($dt) {
		if ($dt instanceof DateTime) {
			return $dt->format('YmdHis');
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}

	/**
	 * Create DateTime object from timestamp (YmdHi).
	 * 
	 * @param string $ts Timestamp value.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed DateTime object or timestamp.
	 */
	public function toDatetimeFromYmdHi($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = new DateTime($ts);
			return $dt;
		}
		return null;
	}

	/**
	 * Create timestamp (YmdHi) from DateTime object.
	 * 
	 * @param DateTime $dt DateTime object.
	 * 
	 * @return int Timestamp in seconds.
	 */
	public function fromDatetimeToYmdHi($dt) {
		if ($dt instanceof DateTime) {
			return $dt->format('YmdHi');
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}

	/**
	 * Create DateTime object from timestamp (Ymd).
	 * 
	 * @param string $ts Timestamp value.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed DateTime object or timestamp.
	 */
	public function toDatetimeFromYmd($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = DateTime::createFromFormat('YmdHi', $ts . date('Hi'));
			return $dt;
		}
		return null;
	}

	/**
	 * Create timestamp (Ymd) from DateTime object.
	 * 
	 * @param DateTime $dt DateTime object.
	 * 
	 * @return int Timestamp in seconds.
	 */
	public function fromDatetimeToYmd($dt) {
		if ($dt instanceof DateTime) {
			return $dt->format('Ymd');
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}

	/**
	 * Create DateTime object from timestamp (Hi).
	 * 
	 * @param string $ts Timestamp value.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed DateTime object or timestamp.
	 */
	public function toDatetimeFromHi($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = DateTime::createFromFormat('YmdHi', date('Ymd') . $ts);
			return $dt;
		}
		return null;
	}

	/**
	 * Create timestamp (Hi) from DateTime object.
	 * 
	 * @param DateTime $dt DateTime object.
	 * 
	 * @return int Timestamp in seconds.
	 */
	public function fromDatetimeToHi($dt) {
		if ($dt instanceof DateTime) {
			return $dt->format('Hi');
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}

	/**
	 * Create bool from value.
	 * 
	 * @param mixed $value Typically 0 or 1.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed Bool or raw value.
	 */
	public function toBoolFromValue($value, $raw = false) {
		if ($raw)
			return $value;
		else {
			return (bool)$value;
		}
	}

	/**
	 * Convert to int from bool.
	 * 
	 * @param bool $value Bool value.
	 * 
	 * @return int Numerical representation of bool.
	 */
	public function fromBoolToNumber($value) {
		if ($value) {
			if ($value === null || $value <= 0)
				return 0;
			else
				return 1;
		}
		else
			return 0;
	}

	/**
	 * Convert to Array from a 'padded' CSV line.
	 * Padded CSV refers to the CSV string being padded with a extra comma at the beginning and end.
	 * This helps with running queries so you can look for an exact value.
	 * For example `field_name LIKE '%for%'` might return a resulr 'for' and'foreman'.
	 * With padding you can try `field_name LIKE '%,for,%'` and only get results with 'for' instead of ones with 'foreman' as well.
	 * 
	 * @param string $value Array values.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed Array of data or raw value.
	 */
	public function toArrayFromPaddedCSV($value, $raw = false) {
		if ($raw)
			return $value;
		if ($value) {
			$parts = explode(',', trim($value, ','));
			$partsSize = sizeof($parts);
			$newValue = [];
			for ($i = 0; $i < $partsSize; $i++) {
				$partsSplit = explode('|', $parts[$i]);
				if (sizeof($partsSplit) > 1)
					$newValue[$partsSplit[0]] = $partsSplit[1];
				else
					$newValue[] = $partsSplit[0];
			}
			return $newValue;
		}
		else
			return null;
	}

	/**
	 * Convert to 'padded' CSV from array.
	 * 
	 * @param Array $value Array of data.
	 * 
	 * @return string Padded CSV data.
	 */
	public function fromArrayToPaddedCSV($value) {
		$newValue = '';
		if ($value) {
			if (is_array($value)) {
				if (\lynk\isAssoc($value)) {
					foreach ($value as $key => $val) {
						$newValue .= "{$key}|{$val},";
					}
				}
				else {
					foreach ($value as  $val) {
						$newValue .= "{$val},";
					}
				}
				if ($newValue != '')
					$newValue = ",{$newValue}";
				return $newValue;
			}
			else {
				if (!\lynk\startsWith($value, ','))
					$value = ",{$value}";
				if (!\lynk\endsWith($value, ','))
					$value = "{$value},";
				return $value;
			}
		}
		else
			return null;
	}

	/**
	 * Convert to Array from a 'padded' CSV line.
	 * 
	 * @param string $value Array values.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed Array of data or raw value.
	 */
	public function toArrayFromCSV($value, $raw = false) {
		if ($raw)
			return $value;
		if ($value) {
			$parts = explode(',', $value);
			$partsSize = sizeof($parts);
			$newValue = [];
			for ($i = 0; $i < $partsSize; $i++) {
				$partsSplit = explode('|', $parts[$i]);
				if (sizeof($partsSplit) > 1)
					$newValue[$partsSplit[0]] = $partsSplit[1];
				else
					$newValue[] = $partsSplit[0];
			}
			return $newValue;
		}
		else
			return null;
	}

	/**
	 * Convert to 'padded' CSV from array.
	 * 
	 * @param Array $value Array of data.
	 * 
	 * @return string Padded CSV data.
	 */
	public function fromArrayToCSV($value) {
		$newValue = '';
		if ($value) {
			if (is_array($value)) {
				if (\lynk\isAssoc($value)) {
					foreach ($value as $key => $val) {
						$newValue .= "{$key}|{$val},";
					}
					$newValue = rtrim($newValue, ',');
				}
				else {
					foreach ($value as  $val) {
						$newValue .= "{$val},";
					}
					$newValue = rtrim($newValue, ',');
				}
				return $newValue;
			}
			else
				return $value;
		}
		else
			return null;
	}

	/**
	 * Convert to array from JSON.
	 * 
	 * @param string $value JSON code.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return Array Array of values.
	 */
	public function toArrayFromJSON($value, $raw = false) {
		if ($raw)
			return $value;
		if ($value)
			return json_decode($value, true);
		else
			return null;
	}

	/**
	 * Convert to object from JSON.
	 * 
	 * @param string $value JSON code.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return stdClass Object of values.
	 */
	public function toClassFromJSON($value, $raw = false) {
		if ($raw)
			return $value;
		if ($value)
			return json_decode($value);
		else
			return null;
	}

	/**
	 * Convert to JSON from array.
	 * 
	 * @param Array Data array.
	 * 
	 * @return string JSON code.
	 */
	public function fromArrayToJSON($value) {
		if ($value) {
			if (is_array($value) || is_object($value))
				return json_encode($value);
			else
				return $value;
		}
		else
			return null;
	}

	/**
	 * Convert to array from YAML code.
	 * 
	 * @param string YAML code.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return Array Array of values.
	 */
	public function toArrayFromYaml($value, $raw = false) {
		if ($raw)
			return $value;
		if ($value) {
			if (static::$isSymfonyYamlSupported) {
				if ($value != '')
					return Yaml::parse(str_replace("\t", "    ", $value));
				else
					return [];
			}
			else {
				return $this->toArrayFromJSON($value);
			}
		}
		else
			return null;
	}

	/**
	 * Convert to YAML code from array.
	 * 
	 * @param Array Data array.
	 * 
	 * @return string YAML code.
	 */
	public function fromArrayToYaml($value) {
		if ($value) {
			if (is_array($value) || is_object($value)) {
				if (static::$isSymfonyYamlSupported) {
					return Yaml::dump($value);
				}
				else {
					return $this->fromArrayToJSON($value);
				}
			}
			else
				return $value;
		}
		else
			return null;
	}

	/**
	 * Convert to object from serialized object.
	 * 
	 * @param string Serialized code.
	 * @param bool $raw Optional. Return raw non-converted value.
	 * 
	 * @return mixed Unserialized result.
	 */
	public function toObjectFromSerialized($value, $raw = false) {
		if ($raw)
			return $value;
		if ($value) {
			if ($this->isSerializedObject($value))
				return unserialize($value);
			else
				return null;
		}
		else
			return null;
	}

	/**
	 * Convert to serialized string from value.
	 * 
	 * @param mixed $value Data to convert.
	 * 
	 * @return string Serialized data.
	 */
	public function fromObjectToSerialized($value) {
		if ($value) {
			if (!$this->isSerializedObject($value))
				return serialize($value);
			else
				return $value;
		}
		else
			return null;
	}
}