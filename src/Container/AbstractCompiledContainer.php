<?php
namespace BGStudios\Component\Container;

abstract class AbstractCompiledContainer {
	private $__container;
	public function registerContainer(Container $container) {
		$this->__container = $container;
	}
	public function get($id) {
		return $this->__container->get($id);
	}
	public function has($id) {
		return $this->__container->has($id);
	}
	public function getContainer() {
		return $this->__container;
	}
	public function map() {}
}