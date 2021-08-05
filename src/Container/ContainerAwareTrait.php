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
 * @subpackage Container
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Container;

/**
 * Container aware trait.
 * Currently uses a shared static container, to be updated to accept a container after constructed.
 */
trait ContainerAwareTrait {

	/**
	 * @var Container Service container.
	 */
	protected static $__container;

	/**
	 * Get service from container.
	 * 
	 * @param string $id Service key.
	 * 
	 * @return mixed Service value.
	 */
	public function getService($key) {
		if ($this->hasService($key))
			return static::$__container[$key];
	}

	/**
	 * Set service on container.
	 * 
	 * @param string $id Service key.
	 * @param mixed $service Service to set.
	 */
	public function setService($key, $service) {
		static::$__container[$key] = $service;
	}

	/**
	 * Check if service exists in container.
	 * 
	 * @param string $id Service key.
	 * 
	 * @return bool True if service exists, false otherwise.
	 */
	public function hasService($key) {
		return static::$__container->has($key);
	}

	/**
	 * Get parameter.
	 * 
	 * @param string $key Parameter key.
	 * 
	 * @return mixed Parameter value.
	 */
	public function getParameter($key) {
		if (static::$__container && static::$__container->hasParameter($key))
			return static::$__container->getParameter($key);
	}

	/**
	 * Set parameter.
	 * 
	 * @param string $key Parameter key.
	 * @param mixed $parameter Parameter value.
	 */
	public function setParameter($key, $parameter) {
		if (static::$__container)
			static::$__container->setParameter($key, $parameter);
	}

	/**
	 * Check if parameter exists.
	 * 
	 * @param string $key Parameter key.
	 * 
	 * @return bool True if parameter exists, false otherwise.
	 */
	public function hasParameter($key) {
		return (static::$__container && static::$__container->hasParameter($key));
	}

	/**
	 * Get container.
	 * 
	 * @return Container Service container.
	 */
	public function getContainer() {
		return static::__getContainer();
	}

	/**
	 * Static set container method.
	 * 
	 * @param Container $container Service container.
	 */
	public static function __setContainer($container) {
		static::$__container = $container;
	}

	/**
	 * Static get container.
	 * 
	 * @return Container Service container.
	 */
	public static function __getContainer() {
		return static::$__container;
	}
}