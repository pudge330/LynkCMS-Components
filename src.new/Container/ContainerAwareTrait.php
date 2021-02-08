<?php
namespace LynkCMS\Component\Container;

trait ContainerAwareTrait {
	protected static $__container;
	public function getService($key) {
		if ($this->hasService($key))
			return static::$__container[$key];
	}
	public function setService($key, $service) {
		static::$__container[$key] = $service;
	}
	public function hasService($key) {
		return static::$__container->has($key);
	}
	public function getParameter($key) {
		if (static::$__container && static::$__container->hasParameter($key))
			return static::$__container->getParameter($key);
	}
	public function setParameter($key, $parameter) {
		if (static::$__container)
			static::$__container->setParameter($key, $parameter);
	}
	public function hasParameter($key) {
		return (static::$__container && static::$__container->hasParameter($key));
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