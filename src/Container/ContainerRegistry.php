<?php

namespace Lynk\Component\Container;

/**
 * Stores the shared service container used by container-aware objects.
 */
final class ContainerRegistry {

	/**
	 * @var Container|null Shared service container.
	 */
	private static $container;

	/**
	 * Set the shared service container.
	 *
	 * @param Container $container Service container.
	 */
	public static function setContainer(Container $container) {
		self::$container = $container;
	}

	/**
	 * Get the shared service container.
	 *
	 * @return Container|null Service container.
	 */
	public static function getContainer() {
		return self::$container;
	}
}
