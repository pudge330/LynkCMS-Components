<?php
namespace BGStudios\Component\Service;

class Service {
	use ServiceTrait;
	private $__config = Array();
	public function __construct() {
	}
	public function setConfig($key, $config = null) {
		if (is_array($key))
			$this->__config = $config ? $key : array_merge($this->__config, $key);
		else
			$this->__config[$key] = $config;
	}
	public function getConfig($key) {
		if ($this->hasConfig($key))
			return $this->__config[$key];
	}
	public function hasConfig($key) {
		return array_key_exists($key, $this->__config);
	}
	public function removeConfig($key) {
		unset($this->__config[$key]);
	}
	public function getAllConfig() {
		return $this->__config;
	}
	public function getConfigKeys() {
		return array_keys($this->__config);
	}
	public function eachConfig($callback) {
		$index = -1;
		foreach ($this->__config as $okey => &$oval) {
			$index++;
			$callback($index, $okey, $oval);
		}
	}
}