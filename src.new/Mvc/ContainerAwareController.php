<?php
namespace LynkCMS\Component\Mvc;

class ContainerAwareController {
	protected $__container;
	protected function getService($key) {
		if ($this->hasService($key))
			return $this->__container[$key];
	}
	protected function setService($key, $service) {
		$this->__container[$key] = $service;
	}
	protected function hasService($key) {
		return $this->__container->has($key);
	}
	protected function getParameter($key) {
		if ($this->__container && $this->__container->hasParameter($key))
			return $this->__container->getParameter($key);
	}
	protected function setParameter($key, $parameter) {
		if ($this->__container)
			$this->__container->setParameter($key, $parameter);
	}
	protected function hasParameter($key) {
		return ($this->__container && $this->__container->hasParameter($key));
	}
	protected function getContainer() {
		return $this->__container;
	}
	public function setContainer($container) {
		$this->__container = $container;
	}
}