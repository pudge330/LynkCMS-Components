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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Abstract command class that add a easier way to run other commands in once call.
 */
class AbstractCommand extends Command {

	/**
	 * Run command.
	 *
	 * @param string $cmd The command to run.
	 * @param Array $input An array of input arguments and options for the command.
	 * @param Symfony\Component\Console\Output\OutputInterface $output Instance of Symfony OutputInterface.
	 * @param int Command return/exit code.
	 */
	public function runCommand($cmd, $input, OutputInterface $output) {
		$command = $this->getApplication()->find($cmd);
		$input = ['command' => $cmd] + $input;
		$input = new ArrayInput($input);
		$returnCode = $command->run($input, $output);
		return $returnCode;
	}
}