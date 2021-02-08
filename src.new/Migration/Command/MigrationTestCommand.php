<?php
namespace LynkCMS\Component\Migration\Command;

use Datetime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationTestCommand extends AbstractCommand {
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:test')
			->setDescription('')
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output) {
		$manager = $this->manager;
	}
}
