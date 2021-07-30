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
 * Container aware class.
 * Currently uses a shared static container, to be updated to accept a container after constructed.
 */
class ContainerAwareClass {
	use ContainerAwareTrait;
}