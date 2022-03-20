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
 * Generate migration command.
 */
class MigrationGenerateCommand extends AbstractCommand {

	/**
	 * Configure command.
	 */
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:generate')
			->setDescription('Generates a new migration class')
			->addArgument('version', InputArgument::OPTIONAL, 'Specific version')
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
		$version = $input->getArgument('version') ?: date('YmdHis');
		$file = rtrim($manager->getPath(), '/') . "/Migration{$version}.php";
		if (!file_exists($file)) {
			if (file_put_contents($file, $manager->generateClass($version))) {
				$output->writeln('Migration file created');
			}
			else {
				$output->writeln('<fg=red>Creating migration file failed</>');
			}
		}
		else {
			$output->writeln("<fg=red>Migration file 'Migration{$version}' already exists</>");
		}
	}
}
