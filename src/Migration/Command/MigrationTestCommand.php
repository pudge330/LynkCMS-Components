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
 * @subpackage Migration
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Migration\Command;

use Datetime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Migration system test.
 */
class MigrationTestCommand extends AbstractCommand {

	/**
	 * Configure the command.
	 */
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:test')
			->setDescription('')
		;
	}

	/**
	 * Execute command.
	 * 
	 * @param Symfony\Component\Console\Input\InputInterface $input Input interface.
	 * @param Symfony\Component\Console\Output\OutputInterface $output Output interface.
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$manager = $this->manager;
		if ($manager->initMigrations()) {
			$output->writeln('<fg=green>Migrations OK</>');
		}
		else {
			$output->writeln('<fg=red>Migrations FAIL</>');
		}
	}
}
