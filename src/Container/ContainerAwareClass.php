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
 * Container aware class.
 * Currently uses a shared static container, to be updated to accept a container after constructed.
 */
class ContainerAwareClass {
	use ContainerAwareTrait;
}