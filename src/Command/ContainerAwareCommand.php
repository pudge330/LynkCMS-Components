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
 * @subpackage Command
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Command;

use LynkCMS\Component\Container\ContainerAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Container aware base command class.
 */
class ContainerAwareCommand extends AbstractCommand {
	use ContainerAwareTrait;
}