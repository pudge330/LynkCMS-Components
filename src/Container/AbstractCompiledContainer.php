<?php
/**
 * This file is part of the Lynk Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Lynk Components
 * @subpackage Container
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Container;

/**
 * Precompiled service container.
 * Eliminates setter calls on aprent container and instead used pre-written methods to construct and return services.
 */
abstract class AbstractCompiledContainer {

	/**
	 * @var Container Parent service container.
	 */
	private $__container;

	/**
	 * Register service container.
	 * 
	 * @param Container Parent service container.
	 */
	public function registerContainer(Container $container) {
		$this->__container = $container;
	}

	/**
	 * Get service from parent container.
	 * 
	 * @param string $id Service key.
	 * 
	 * @return mixed Service value.
	 */
	public function get($id) {
		return $this->__container->get($id);
	}

	/**
	 * Check if service exists in parent container.
	 * 
	 * @param string $id Service key.
	 * 
	 * @return bool True if service exists, false otherwise.
	 */
	public function has($id) {
		return $this->__container->has($id);
	}

	/**
	 * Get parent container.
	 * 
	 * @return Container Parent service container.
	 */
	public function getContainer() {
		return $this->__container;
	}

	/**
	 * Method to service map.
	 * 
	 * @return Array Method call to service map.
	 */
	public function map() {}
}