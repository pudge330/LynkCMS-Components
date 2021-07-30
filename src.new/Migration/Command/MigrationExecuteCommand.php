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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Execute migration command.
 */
class MigrationExecuteCommand extends AbstractCommand {

	/**
	 * Configure the command.
	 */
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:execute')
			->setDescription('Run a specific version.')
			->addArgument('version', InputArgument::REQUIRED, 'Version migration to run.')
			->addOption('up', null, InputOption::VALUE_NONE, 'Run migration up.')
			->addOption('down', null, InputOption::VALUE_NONE, 'Run migration down.')
			->addOption('all', null, InputOption::VALUE_NONE, 'Run all migration up or down to the specified version.')
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
		$up = $input->getOption('up');
		$down = $input->getOption('down');
		$all = $input->getOption('all');
		if ($up && $down) {
			$down = false;
		}
		else if (!$up && !$down) {
			$up = true;
		}

		$r = function($s, $m) {
			return str_repeat($s, $m);
		};
		$s = function($m) {
			return str_repeat(' ', $m);
		};
		$p = function($s, $total, $fill = ' ', $side = 'both') {
			if ($side == 'both') {
				$l = strlen($s);
				$lpadding = $l < $total ? floor(($total - $l) / 2) : 0;
				$rpadding = $l < $total ? ceil(($total - $l) / 2) : 0;
				return str_repeat($fill, $lpadding) . $s . str_repeat($fill, $rpadding);
			}
			else {
				$l = strlen($s);
				$padding = $l < $total ? $total - $l : 0;
				return $side == 'left'
					? str_repeat($fill, $padding) . $s
					: $s . str_repeat($fill, $padding);
			}
		};
		$helper = $this->getHelper('question');
		$allCall = $up ? 'migrationUpCount' : 'migrationDownCount';
		$count = $all ? $manager->{$allCall}($version) : $manager->migrationCount($version, $up);
		if ($up && $manager->hasVersion($version)) {
			$output->writeln("\nThis migration has already been run.\n");
			return;
		}
		if (!$all) {
			$count['migrations'] = 1;
		}
		$output->writeln('');
		$output->writeln('<bg=cyan;fg=black>' . $r(' ', 60) . '</>');
		if ($up) {
			$tmp = Array(
				$p("You are about to run {$count['migrations']} migration(s) UP composed of", 60),
				$p("{$count['queries']} queries and {$count['actions']} actions.", 60),
			);
		}
		else {
			$tmp = Array(
				"    You are about to run {$count['migrations']} migration(s) DOWN composed of    ",
				"     {$count['queries']} queries and {$count['actions']} actions." . $s(31),
			);
		}
		$output->writeln('<bg=cyan;fg=black>' . implode("</>\n<bg=cyan;fg=black>", $tmp) . '</>');
		$question = new ConfirmationQuestion(
			"Are you sure you want to continue (y|n)? ",
			false
		);
		$output->writeln('<bg=cyan;fg=black>' . $r(' ', 60) . '</>');
		$output->writeln('');
		if (!$helper->ask($input, $output, $question)) {
			$output->writeln('');
            return;
        }

        $output->writeln('');

        if ($all) {
        	$executeCall = $up ? 'migrateUp' : 'migrateDown';
	        $result = $manager->{$executeCall}($version, ($all ? false : true));
        }
        else {
	        $result = $manager->execute($version, $up);
        }

        if ($result) {

	        $output->writeln($r('-', 60));
			$output->writeln(
				sizeof($result['successful']) . " successful, " .
				sizeof($result['successfulQueries']) . ' queries and ' .
				sizeof($result['successfulActions']) . ' actions'
			);
			$output->writeln(
				sizeof($result['failed']) . " failed, " .
				sizeof($result['failedQueries']) . ' queries and ' .
				sizeof($result['failedActions']) . ' actions'
			);
			$output->writeln($r(' ', 60));

		}
		else {
			$output->writeln($r('-', 60));
			$output->writeln('Error running migration(s)');
			$output->writeln($r(' ', 60));
		}
	}
}
