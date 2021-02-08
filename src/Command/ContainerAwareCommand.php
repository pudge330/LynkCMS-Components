<?php
/**
 * This file is part of the BGStudios Command Component.
 *
 * (c) Brandon Garcia <brandon@bgstudios.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package BGStudios PHP Components
 * @subpackage Command
 * @author Brandon Garcia <brandon@bgstudios.io>
 */

namespace BGStudios\Component\Command;

use BGStudios\Component\Container\ContainerAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Container aware base command class.
 */
class ContainerAwareCommand extends AbstractCommand {
	use ContainerAwareTrait;
}