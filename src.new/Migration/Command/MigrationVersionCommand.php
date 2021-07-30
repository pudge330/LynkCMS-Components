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

use Datetime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Migration version command.
 */
class MigrationVersionCommand extends AbstractCommand {

	/**
	 * Configure command.
	 */
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:version')
			->setDescription('Manually add or remove a version from the migrations table')
			->addArgument('version', InputArgument::REQUIRED, 'Version to add or remove')
			->addArgument('action', InputArgument::OPTIONAL, 'Whether to add or remove. Add: add or true. Remove: remove or false')
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
		$version = $input->getArgument('version');
		$action = $input->getArgument('action') ?: 'add';
		$result = null;
		if (in_array($action, Array('add', 'true'))) {
			$result = $manager->addVersion($version);
			$action = 'add';
		}
		else if (in_array($action, Array('remove', 'false'))) {
			$result = $manager->removeVersion($version);
			$action = 'remove';
		}
		if ($result) {
			$output->writeln("Migration version {$version} " . ($action == 'add' ? 'added' : 'removed') . " successfuly");
		}
		else {
			$output->writeln("<fg=red>Migration version {$version} failed " . ($action == 'add' ? 'adding' : 'removing') . "</>");
		}
	}
}
