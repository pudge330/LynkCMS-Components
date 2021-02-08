<?php
namespace BGStudios\Component\Service;

trait ServiceTrait {
	protected $__services = Array();
	protected static $__container;
	public function getService($key) {
		if ($this->hasService($key)) {
			if (array_key_exists($key, $this->__services))
				return $this->__services[$key];
			else
				return static::$__container[$key];
		}
	}
	public function setService($key, $service) {
		$this->__services[$key] = $service;
	}
	public function hasService($key) {
		if (static::$__container->has($key) ||
			($this->__services && array_key_exists($key, $this->__services)))
			return true;
		else
			return false;
	}
	public function getParameter($key) {
		if ($this->hasParameter($key))
			return static::$__container->getParameter($key);
		return null;
	}
	public function setParameter($key, $parameter) {
		if (static::$__container)
			static::$__container->setParameter($key, $parameter);
	}
	public function hasParameter($key) {
		return (static::$__container && static::$__container->hasParameter($key));
	}
	public function removeService($key) {
		unset($this->__services[$key]);
	}
	public function getAllServices() {
		return array_merge(static::$__container, $this->__services);
	}
	public function getServiceKeys() {
		return array_merge(static::$__container->keys(), array_keys($this->__services));
	}
	public function eachService($callback) {
		$index = -1;
		foreach (static::$__container as $skey => &$sval) {
			$index++;
			$callback($index, $skey, $sval);
		}
		foreach ($this->__services as $skey => &$sval) {
			$index++;
			$callback($index, $skey, $sval);
		}
	}
	public function getContainer() {
		return static::__getContainer();
	}
	public static function __setContainer($container) {
		static::$__container = $container;
	}
	public static function __getContainer() {
		return static::$__container;
	}
}