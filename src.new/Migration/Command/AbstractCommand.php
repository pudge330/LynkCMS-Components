<?php
namespace LynkCMS\Component\Migration\Command;

use LynkCMS\Component\Migration\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractCommand extends Command {
	protected $manager;
	protected $prefix;
	public function __construct(Manager $manager, $prefix = '') {
		$this->manager = $manager;
		$this->prefix = $prefix;
		parent::__construct();
	}
	public function runCommand($cmd, $input, $output) {
		$command = $this->getApplication()->find($cmd);
		$input = ['command' => $cmd] + $input;
		$input = new ArrayInput($input);
		$returnCode = $command->run($input, $output);
		return $returnCode;
	}
	protected function initialize(InputInterface $input, OutputInterface $output) {		
	}
	protected function interact(InputInterface $input, OutputInterface $output) {
	}
}

