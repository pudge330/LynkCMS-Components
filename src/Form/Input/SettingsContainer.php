<?php
namespace BGStudios\Component\Form\Input;

class SettingsContainer {
	protected $data;
	public function __construct($data = []) {
		$this->data = [];
		$this->merge($data);
	}
	public function __get($key) {
		return $this->get($key);
	}
	public function __set($key, $value) {
		$this->set($key, $value);
	}
	public function __call($method, $args) {
		if ($this->has($method)) {
			$func = $this->get($method);
			return call_user_func_array($func, $args);
		}
		else
			return null;
	}
	public function get($key) {
		if ($this->has($key))
			return $this->data[$key];
		else
			return null;
	}
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	public function merge($data) {
		if (is_array($data)) {
			$this->data = array_merge($this->getData(), $data);
		}
		else if (is_object($data)) {
			foreach ($data as $dkey => $dval) {
				$this->set($deky, $dval);
			}
		}
	}
	public function has($key) {
		if (array_key_exists($key, $this->data))
			return true;
		else
			return false;
	}
	public function isnull($key) {
		if ($this->has($key)) {
			if (is_null($this->get($key)))
				return true;
			else
				return false;
		}
		else
			return true;
	}
	public function isempty($key) {
		if ($this->has($key)) {
			$value = $this->get($key);
			if (is_null($value) || empty($value) || $value == '')
				return true;
			else
				return false;
		}
		else
			return true;
	}
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
	public function remove($key) {
		unset($this->data[$key]);
	}
	public function getData() {
		return $this->data;
	}
	public function toJSON() {
		if (version_compare(phpversion(), '5.4', '<'))
			return json_encode($this->getDataWithoutClosures());
		else //--JSON_PRETTY_PRINT is available
			return json_encode($this->getDataWithoutClosures(), JSON_PRETTY_PRINT);
	}
	public function toSerialize() {
		return serialize($this->getDataWithoutClosures());
	}
	public function getDataWithoutClosures($obj = null) {
		$obj = $obj ? $obj->getData() : $this->getData();
		$arr = [];
		foreach ($obj as $key => $val) {
			if (!($val instanceof \Closure)) {
				$arr[$key] = $val;
			}
		}
		return $arr;
	}
	public static function convertArrays($a) {
		foreach ($a as $akey => &$aval) {
			$aval = static::convertArray($aval);
		}
		return $a;
	}
	public static function convertArray($arr) {
		return new SettingsContainer($arr);
	}
}