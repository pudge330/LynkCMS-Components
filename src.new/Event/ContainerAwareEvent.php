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
 * @subpackage Event
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Event;

use LynkCMS\Component\Container\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base container aware event class.
 */
class ContainerAwareEvent extends Event {
	use ContainerAwareTrait;
}