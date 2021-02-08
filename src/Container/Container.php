<?php
namespace BGStudios\Component\Container;

use Pimple\Container as BaseContainer;
use Pimple\Exception\UnknownIdentifierException;

class Container extends BaseContainer {
	private $parameters = Array();
	protected $compiledContainer;
	protected $compiledContainerMap;
	protected $compiledContainerMapKeys;
	public function __construct(Array $values = [], AbstractCompiledContainer $compiledContainer = null) {
		parent::__construct($values);
		if ($compiledContainer)
			$this->setCompiledContainer($compiledContainer);
		if (!$this->compiledContainerMapKeys)
			$this->compiledContainerMapKeys = Array();
	}
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
	public function offsetExists($id) {
		$exists = parent::offsetExists($id);
		if (!$exists) {
			$exists = in_array($id, $this->compiledContainerMapKeys);
		}
		return $exists;
	}
	public function offsetUnset($id) {
		parent::offsetUnset($id);
		unset($this->compiledContainerMap[$id]);
		$this->compiledContainerMapKeys = array_keys($this->compiledContainerMap);
	}
	public function keys() {
		$keys = array_merge($this->compiledContainerMapKeys, parent::keys());
		$keys = array_unique($keys);
		$keys = array_values($keys);
		return $keys;
	}
	public function get($id) {
		return $this->offsetGet($id);
	}
	public function set($id, $value) {
		$this->offsetSet($id, $value);
	}
	public function has($id) {
		if ($this->offsetExists($id))
			return true;
		else
			return false;
	}
	public function reset($id, $service) {
		$this->remove($id);
		$this->set($id, $service);
	}
	public function remove($id) {
		$this->offsetUnset($id);
	}
	public function setParameter($key, $parameter) {
		$this->parameters[$key] = $parameter;
	}
	public function getParameter($key) {
		if ($this->hasParameter($key))
			return $this->parameters[$key];
	}
	public function hasParameter($key) {
		if (array_key_exists($key, $this->parameters))
			return true;
		else
			return false;
	}
	public function removeParameter($key) {
		unset($this->parameters[$key]);
	}
	public function getAllParameters() {
		return $this->parameters;
	}
	public function getParameterKeys() {
		return array_keys($this->parameters);
	}
	public function eachParameter($callback) {
		$index = -1;
		foreach ($this->parameters as $pkey => &$pval) {
			$index++;
			$callback($index, $pkey, $pval);
		}
	}
}