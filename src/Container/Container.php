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

use Closure;
use Pimple\Container as BaseContainer;
use Pimple\Exception\UnknownIdentifierException;

/**
 * Service container class, built of of the Pimple container.
 */
class Container extends BaseContainer {

	/**
	 * @var Array Parameters list.
	 */
	private $parameters = Array();

	/**
	 * @var AbstractCompiledContainer Precompiled container.
	 */
	protected $compiledContainer;

	/**
	 * @var Array Compiled container service map.
	 */
	protected $compiledContainerMap;

	/**
	 * @var Array Compiled container service map keys.
	 */
	protected $compiledContainerMapKeys;

	/**
	 * @param Array $values Optional. Container key value pairs.
	 * @param AbstractCompiledContainer $compiledContainer Optional. Precompiled container.
	 */
	public function __construct(Array $values = [], AbstractCompiledContainer $compiledContainer = null) {
		parent::__construct($values);
		if ($compiledContainer)
			$this->setCompiledContainer($compiledContainer);
		if (!$this->compiledContainerMapKeys)
			$this->compiledContainerMapKeys = Array();
	}

	/**
	 * Set compiled container.
	 * 
	 * @param AbstractCompiledContainer Precompiled container.
	 */
	public function setCompiledContainer(AbstractCompiledContainer $container) {
		$this->compiledContainer = $container;
		$this->compiledContainerMap = $this->compiledContainer
			? $this->compiledContainer->map()
			: Array();
		$this->compiledContainerMapKeys = array_keys($this->compiledContainerMap);
		if ($this->compiledContainer) {
			$this->compiledContainer->registerContainer($this);
		}
	}

	/**
	 * Get array offset value.
	 * 
	 * @param string $id Offset key.
	 * 
	 * @return mixed Offset value.
	 * 
	 * @throws UnknownIdentifierException
	 */
	public function offsetGet($id) {
		if (parent::offsetExists($id)) {
			return parent::offsetGet($id);
		}
		else if ($this->compiledContainer) {
			if (array_key_exists($id, $this->compiledContainerMap)) {
				$value = $this->compiledContainer->{$this->compiledContainerMap[$id]}();
				$this->offsetSet($id, $value);
				return $value;
			}
			else {
				throw new UnknownIdentifierException($id);
			}
		}
		else {
			throw new UnknownIdentifierException($id);
		}
	}

	/**
	 * Check if array offset key exists.
	 * 
	 * @param string $id Offset key.
	 * 
	 * @return bool True if key exists, false otherwise.
	 */
	public function offsetExists($id) {
		$exists = parent::offsetExists($id);
		if (!$exists) {
			$exists = in_array($id, $this->compiledContainerMapKeys);
		}
		return $exists;
	}

	/**
	 * Unset array offset.
	 * 
	 * @param string $id Offset key.
	 */
	public function offsetUnset($id) {
		parent::offsetUnset($id);
		unset($this->compiledContainerMap[$id]);
		$this->compiledContainerMapKeys = array_keys($this->compiledContainerMap);
	}

	/**
	 * Get service keys.
	 * 
	 * @return Array Key list.
	 */
	public function keys() {
		$keys = array_merge($this->compiledContainerMapKeys, parent::keys());
		$keys = array_unique($keys);
		$keys = array_values($keys);
		return $keys;
	}

	/**
	 * Get service.
	 * 
	 * @param string $id Service key.
	 * 
	 * @return mixed Service value.
	 */
	public function get($id) {
		return $this->offsetGet($id);
	}

	/**
	 * Set service.
	 * 
	 * @param string $id Service key.
	 * @param mixed $value Service value.
	 */
	public function set($id, $value) {
		$this->offsetSet($id, $value);
	}

	/**
	 * Check if service exists.
	 * 
	 * @param string $id Service key.
	 * 
	 * @return bool True if service exists, false otherwise.
	 */
	public function has($id) {
		if ($this->offsetExists($id))
			return true;
		else
			return false;
	}

	/**
	 * Reset service. Removes existing service, then sets it again.
	 * 
	 * @param string $id Service key.
	 * @param mixed $service Service value.
	 */
	public function reset($id, $service) {
		$this->remove($id);
		$this->set($id, $service);
	}

	/**
	 * Remove service.
	 * 
	 * @param string $id Service key.
	 */
	public function remove($id) {
		$this->offsetUnset($id);
	}

	/**
	 * Set parameter.
	 * 
	 * @param string $key Parameter key.
	 * @param mixed $parameter Parameter value.
	 */
	public function setParameter($key, $parameter) {
		$this->parameters[$key] = $parameter;
	}

	/**
	 * Get parameter.
	 * 
	 * @param string $key Parameter key.
	 * 
	 * @return mixed Parameter value.
	 */
	public function getParameter($key) {
		if ($this->hasParameter($key))
			return $this->parameters[$key];
	}

	/**
	 * Check if parameter exists.
	 * 
	 * @param string $key Parameter key.
	 * 
	 * @return bool True if parameter exists, false otherwise.
	 */
	public function hasParameter($key) {
		if (array_key_exists($key, $this->parameters))
			return true;
		else
			return false;
	}

	/**
	 * Remove parameter.
	 * 
	 * @param string $key Parameter key.
	 */
	public function removeParameter($key) {
		unset($this->parameters[$key]);
	}

	/**
	 * Get all parameters.
	 * 
	 * @return Array Parameter list.
	 */
	public function getAllParameters() {
		return $this->parameters;
	}

	/**
	 * Get parameter keys.
	 * 
	 * @return Array Parameter key list.
	 */
	public function getParameterKeys() {
		return array_keys($this->parameters);
	}

	/**
	 * Run callback for each parameter. Passing in current index, key and value as parameters.
	 * 
	 * @param Closure $closure Callback closure.
	 */
	public function eachParameter(Closure $callback) {
		$index = -1;
		foreach ($this->parameters as $key => $value) {
			$index++;
			$callback($index, $key, $value);
		}
	}
}