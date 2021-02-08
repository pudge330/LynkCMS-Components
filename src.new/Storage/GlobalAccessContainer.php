<?php
namespace LynkCMS\Component\Storage;

class GlobalAccessContainer {
	protected $globalKey;

	public function __construct($key, $data = []) {
		$this->globalKey = $key;
		$this->createContainer();
		$this->merge($data);
	}

	protected function createContainer() {
		if (!isset($GLOBALS[$this->globalKey]))
			$GLOBALS[$this->globalKey] = array();
		else if (!is_array($GLOBALS[$this->globalKey]))
			$GLOBALS[$this->globalKey] = array();
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
			return $GLOBALS[$this->globalKey][$key];
		else
			return null;
	}

	public function set($key, $value) {
		$GLOBALS[$this->globalKey][$key] = $value;
	}

	public function merge($data) {
		if (is_array($data)) {
			$GLOBALS[$this->globalKey] = array_merge($this->export(), $data);
		}
		else if ($data instanceof StandardContainer) {
			$GLOBALS[$this->globalKey] = array_merge($this->export(), $data->export());
		}
		else if (is_object($data)) {
			foreach ($data as $dkey => $dval) {
				$this->set($dkey, $dval);
			}
		}
	}

	public function has($key) {
		if (array_key_exists($key, $GLOBALS[$this->globalKey]))
			return true;
		else
			return false;
	}

	public function isnull($key) {
		if ($this->has($key)) {
			if (is_null($GLOBALS[$this->globalKey][$key]))
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
		unset($GLOBALS[$this->globalKey][$key]);
	}

	public function export() {
		return $GLOBALS[$this->globalKey];
	}

	public function toJSON() {
		if (version_compare(phpversion(), '5.4', '<'))
			return json_encode($this->exportWithoutClosures());
		else //--JSON_PRETTY_PRINT is available
			return json_encode($this->exportWithoutClosures(), JSON_PRETTY_PRINT);
	}

	public function toSerialize() {
		return serialize($this->exportWithoutClosures());
	}

	public function toYAML() {
		return Yaml::dump($this->exportWithoutClosures(), 3);
	}

	public function exportWithoutClosures($obj = null) {
		$obj = $obj ? $obj->export() : $this->export();
		$tmp = [];
		foreach ($obj as $key => $val) {
			if (!($val instanceof \Closure)) {
				$tmp[$key] = $val;
			}
		}
		return $tmp;
	}

	public static function convertArrays($a) {
		foreach ($a as $akey => &$aval) {
			$aval = static::convertArray($aval);
		}
		return $a;
	}

	public static function convertArray($arr) {
		new GlobalAccessContainer($arr);
	}
}