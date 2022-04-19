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
 * @subpackage Command
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Command;

use Lynk\Component\Container\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Container aware base command class.
 */
class ContainerAwareCommand extends AbstractCommand {
	// use ContainerAwareTrait;
	/**
	 * @var Container Service container.
	 */
	protected $__container;

	/**
	 * Get service from container.
	 * 
	 * @deprecated This method is deprecated.
	 *             Instead get container using $this->getContainer() method.
	 * 
	 * @param string $id Service key.
	 * 
	 * @return mixed Service value.
	 */
	public function getService($key) {
		if ($this->hasService($key))
			return $this->$__container[$key];
	}

	/**
	 * Set service on container.
	 * 
	 * @deprecated This method is deprecated.
	 *             Instead get container using $this->getContainer() method.
	 * 
	 * @param string $id Service key.
	 * @param mixed $service Service to set.
	 */
	public function setService($key, $service) {
		$this->$__container[$key] = $service;
	}

	/**
	 * Check if service exists in container.
	 * 
	 * @deprecated This method is deprecated.
	 *             Instead get container using $this->getContainer() method.
	 * 
	 * @param string $id Service key.
	 * 
	 * @return bool True if service exists, false otherwise.
	 */
	public function hasService($key) {
		return $this->$__container->has($key);
	}

	/**
	 * Get parameter.
	 * 
	 * @deprecated This method is deprecated.
	 *             Instead get container using $this->getContainer() method.
	 * 
	 * @param string $key Parameter key.
	 * 
	 * @return mixed Parameter value.
	 */
	public function getParameter($key) {
		if ($this->$__container && $this->$__container->hasParameter($key))
			return $this->$__container->getParameter($key);
	}

	/**
	 * Set parameter.
	 * 
	 * @deprecated This method is deprecated.
	 *             Instead get container using $this->getContainer() method.
	 * 
	 * @param string $key Parameter key.
	 * @param mixed $parameter Parameter value.
	 */
	public function setParameter($key, $parameter) {
		if ($this->$__container)
			$this->$__container->setParameter($key, $parameter);
	}

	/**
	 * Check if parameter exists.
	 * 
	 * @deprecated This method is deprecated.
	 *             Instead get container using $this->getContainer() method.
	 * 
	 * @param string $key Parameter key.
	 * 
	 * @return bool True if parameter exists, false otherwise.
	 */
	public function hasParameter($key) {
		return ($this->$__container && $this->$__container->hasParameter($key));
	}

	/**
	 * Get container.
	 * 
	 * @return Container Service container.
	 */
	public function getContainer() {
		return $this->__container;
	}

	/**
	 * Set container.
	 * 
	 * @param Container Service container.
	 */
	public function setContainer(Container $container) {
		$this->__container = $container;
	}
}