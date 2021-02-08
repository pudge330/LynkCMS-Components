<?php
namespace BGStudios\Component\Migration\Command;

use Datetime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationGenerateCommand extends AbstractCommand {
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:generate')
			->setDescription('Generates a new migration class')
			->addArgument('version', InputArgument::OPTIONAL, 'Specific version')
		;
	}
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
