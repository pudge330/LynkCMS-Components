<?php
namespace BGStudios\Component\Migration\Command;

use Datetime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationVersionCommand extends AbstractCommand {
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:version')
			->setDescription('Manually add or remove a version from the migrations table')
			->addArgument('version', InputArgument::REQUIRED, 'Version to add or remove')
			->addArgument('action', InputArgument::OPTIONAL, 'Whether to add or remove. Add: add or true. Remove: remove or false')
		;
	}
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
