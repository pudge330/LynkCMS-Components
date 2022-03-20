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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Migrate command.
 */
class MigrationMigrateCommand extends AbstractCommand {

	/**
	 * Configure command.
	 */
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:migrate')
			->setDescription('Migrates the system up')
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
		$r = function($s, $m) {
			return str_repeat($s, $m);
		};
		$s = function($m) {
			return str_repeat(' ', $m);
		};
		$helper = $this->getHelper('question');
		$count = $manager->migrationUpCount();
		$output->writeln('');
		$output->writeln('<bg=cyan;fg=black>' . $r(' ', 60) . '</>');
		$tmp = Array(
			"     You are about to run {$count['migrations']} migration(s) UP composed of     ",
			"     {$count['queries']} queries and {$count['actions']} actions." . $s(31),
		);
		$output->writeln('<bg=cyan;fg=black>' . implode("</>\n<bg=cyan;fg=black>", $tmp) . '</>');
		$question = new ConfirmationQuestion("Are you sure you want to continue (y|n)? ");
		$output->writeln('<bg=cyan;fg=black>' . $r(' ', 60) . '</>');
		$output->writeln('');
		if (!$helper->ask($input, $output, $question)) {
			$output->writeln('');
            return;
        }
        $output->writeln('');
		$result = $manager->migrateUp(null, false);
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
}
