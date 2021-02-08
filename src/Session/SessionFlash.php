<?php
namespace BGStudios\Component\Session;

use BGStudios\Component\Storage\GlobalAccessContainer;

class SessionFlash {
	protected $sessionDataKey;
	protected $session;

	public function __construct($key = 'sessionFlash', GlobalAccessContainer $container = null) {
		$this->sessionDataKey = $key;
		$this->session = $container ?: new GlobalAccessContainer('_SESSION');
	}

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

	public function set($key, $value) {
		$flash = $this->export();
		$flash[$key] = $value;
		$this->session->set($this->sessionDataKey, $flash);
	}

	public function has($key) {
		if (array_key_exists($key, $this->export()))
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
			return false;
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
		if ($this->has($key)) {
			$flash = $this->export();
			unset($flash[$key]);
			$this->session->set($this->sessionDataKey, $flash);
		}
	}

	public function export() {
		return $this->session->get($this->sessionDataKey) ?: Array();
	}

	public function toJSON() {
		if (version_compare(phpversion(), '5.4', '<'))
			return json_encode($this->getDataWithoutClosures());
		else //--JSON_PRETTY_PRINT is available
			return json_encode($this->getDataWithoutClosures(), JSON_PRETTY_PRINT);
	}

	public function toSerialize() {
		// $tmp = $this->getNewWithoutClosures();
		return serialize($this->getDataWithoutClosures());
	}

	public function toYAML() {
		return Yaml::dump($this->getDataWithoutClosures(), 3);
	}

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