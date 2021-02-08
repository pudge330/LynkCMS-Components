<?php
namespace LynkCMS\Component\Database;

use DateTime;
use Exception;
use Symfony\Component\Yaml\Yaml;

class AbstractEntity {
	public static $isSymfonyYamlSupported = null;
	protected $__tmpData = Array();
	public function __construct($data = Array(), $tmpData = Array()) {
		if (static::$isSymfonyYamlSupported === null) {
			static::$isSymfonyYamlSupported = class_exists('\Symfony\Component\Yaml\Yaml');
		}
		$data = is_array($data) ? $data : Array();
		$this->set($data);
		$tmpData = is_array($tmpData) ? $tmpData : Array();
		$this->setTmpData($tmpData);
	}
	public function merge($data) {
		$data = is_object($data) ? $data->export() : $data;
		$this->set($data);
	}
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
	public function has($field) {
		if (is_array($field)) {
			$return = Array();
			foreach ($field as $f)
				$return[] = $this->has($f);
			return $return;
		}
		else if ($field) {
			$field = $this->toCamelCase($field);
			return isset($this->$field);
		}
		else
			return null;
	}
	public function setTmpData($key, $value = null) {
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
	public function getTmpData($key) {
		if ($this->hasTmpData($key))
			return $this->__tmpData[$key];
		else
			return null;
	}
	public function hasTmpData($key) {
		return array_key_exists($key, $this->__tmpData);
	}
	public function removeTmpData($key) {
		unset($this->__tmpData[$key]);
	}
	public function exportTmpData() {
		return $this->__tmpData;
	}
	public function export() {
		$data = (array)$this;
		unset($data["\x00*\x00__tmpData"], $data["__tmpData"]);
		return $data;
	}
	public static function hydrate($a) {
		foreach ($a as $akey => &$aval) {
			$aval = new static($aval);
		}
		return $a;
	}
	protected function toCamelCase($string) {
		$parts = explode('_', $string);
		$string = array_shift($parts);
		while (sizeof($parts)) {
			$string .= ucfirst($parts[0]);
			array_shift($parts);
		}
		return $string;
	}
	protected function toProperCase($string) {
		return ucfirst($this->toCamelCase($string));
	}
	protected function isSerializedObject($string) {
		return ($string == serialize(false) || @unserialize($string) !== false);
	}
	protected function doesStringStartWith($haystack, $needle) {
		return preg_match('/^' . preg_quote($needle) . '/', $haystack);
	}
	protected function doesStringEndWith($haystack, $needle) {
		return preg_match('/' . preg_quote($needle) . '$/', $haystack);
	}
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
	public function fromDatetimeToTimestamp($dt) {
		if ($dt instanceof DateTime) {
			return $dt->getTimestamp();
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}
	public function toDatetimeFromYmdHis($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = new DateTime($ts);
			return $dt;
		}
		return null;
	}
	public function fromDatetimeToYmdHis($dt) {
		if ($dt instanceof DateTime) {
			return $dt->format('YmdHis');
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}
	public function toDatetimeFromYmdHi($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = new DateTime($ts);
			return $dt;
		}
		return null;
	}
	public function fromDatetimeToYmdHi($dt) {
		if ($dt instanceof DateTime) {
			return $dt->format('YmdHi');
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}
	public function toDatetimeFromYmd($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = DateTime::createFromFormat('YmdHi', $ts . date('Hi'));
			return $dt;
		}
		return null;
	}
	public function fromDatetimeToYmd($dt) {
		if ($dt instanceof DateTime) {
			return $dt->format('Ymd');
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}
	public function toDatetimeFromHi($ts, $raw = false) {
		if ($raw)
			return $ts;
		if ($ts && is_numeric($ts)) {
			$dt = DateTime::createFromFormat('YmdHi', date('Ymd') . $ts);
			return $dt;
		}
		return null;
	}
	public function fromDatetimeToHi($dt) {
		if ($dt instanceof DateTime) {
			return $dt->format('Hi');
		}
		else if (is_numeric($dt))
			return (int)$dt;
		else
			return null;
	}
	public function toBoolFromValue($value, $raw = false) {
		if ($raw)
			return $value;
		else {
			return (bool)$value;
		}
	}
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
	public function fromArrayToPaddedCSV($value) {
		$newValue = '';
		if ($value) {
			if (is_array($value)) {
				if ($this->isAssociativeArray($value)) {
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
				if (!$this->doesStringStartWith($value, ','))
					$value = ",{$value}";
				if (!$this->doesStringEndWith($value, ','))
					$value = "{$value},";
				return $value;
			}
		}
		else
			return null;
	}

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
	public function fromArrayToCSV($value) {
		$newValue = '';
		if ($value) {
			if (is_array($value)) {
				if ($this->isAssociativeArray($value)) {
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
	public function toArrayFromJSON($value, $raw = false) {
		if ($raw)
			return $value;
		if ($value)
			return json_decode($value, true);
		else
			return null;
	}
	public function toClassFromJSON($value, $raw = false) {
		if ($raw)
			return $value;
		if ($value)
			return json_decode($value);
		else
			return null;
	}
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
	protected function isAssociativeArray($array) {
		return \bgs\isAssoc($array);
	}
}