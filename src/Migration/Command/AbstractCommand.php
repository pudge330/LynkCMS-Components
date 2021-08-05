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
 * @subpackage Migration
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Migration\Command;

use LynkCMS\Component\Migration\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract migration command.
 */
class AbstractCommand extends Command {

	/**
	 * @var Manager Migration manager.
	 */
	protected $manager;

	/**
	 * @var string Command name prefix.
	 */
	protected $prefix;

	/**
	 * @param Manager $manager Migration manager.
	 * @param string $prefix Optional. Command name prefix.
	 */
	public function __construct(Manager $manager, $prefix = '') {
		$this->manager = $manager;
		$this->prefix = $prefix;
		parent::__construct();
	}

	/**
	 * Run a registered command.
	 * 
	 * @param string $cmd Command name.
	 * @param Array $input Command arguments and options.
	 * @param Symfony\Component\Console\Output\OutputInterface $output Output interface.
	 * 
	 * @return int Command return status code.
	 */
	public function runCommand($cmd, $input, OutputInterface $output) {
		$command = $this->getApplication()->find($cmd);
		$input = ['command' => $cmd] + $input;
		$input = new ArrayInput($input);
		$returnCode = $command->run($input, $output);
		return $returnCode;
	}

	/**
	 * Initialize command.
	 * 
	 * @param Symfony\Component\Console\Input\InputInterface $input Input interface.
	 * @param Symfony\Component\Console\Output\OutputInterface $output Output interface.
	 */
	protected function initialize(InputInterface $input, OutputInterface $output) {}

	/**
	 * Interact with user.
	 * 
	 * @param Symfony\Component\Console\Input\InputInterface $input Input interface.
	 * @param Symfony\Component\Console\Output\OutputInterface $output Output interface.
	 */
	protected function interact(InputInterface $input, OutputInterface $output) {}
}

