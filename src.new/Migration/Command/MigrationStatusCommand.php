<?php
namespace LynkCMS\Component\Migration\Command;

use PDO;
use Datetime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationStatusCommand extends AbstractCommand {
	protected function configure() {
		$this
			->setName(($this->prefix ? "{$this->prefix}:" : '') . 'migrations:status')
			->setDescription('Gets the migration system status')
			// ->addArgument(
   //              'subCmd',
   //              InputArgument::OPTIONAL,
   //              'Migration command to run'
   //          )
		;
	}
	protected function execute(InputInterface $input, OutputInterface $output) {
		$manager = $this->manager;
		$dbDriver = $manager->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME);
		$r = function($s, $m) {
			return str_repeat($s, $m);
		};
		$s = function($m) {
			return str_repeat(' ', $m);
		};
		$output->writeln('');
		$output->writeln('<bg=green;fg=black>' . $r('+', 60) . '</>');
		$output->writeln('<bg=green;fg=black>|' . $s(18) . 'Migration Configuration' . $s(17) . '|</>');
		$output->writeln('<bg=green;fg=black>' . $r('+', 60) . '</>');
		$output->writeln('');
		$output->writeln('Database Driver: ' . $s(16) . $dbDriver);
		$output->writeln('Database Host: ' . $s(18) . $manager->getConnection()->getAttribute(\PDO::ATTR_CONNECTION_STATUS));
		$output->writeln('Database Name: ' . $s(18) . $manager->getConnection()->run('SELECT DATABASE()')['data'][0]['DATABASE()']);
		$output->writeln('Version Table: ' . $s(18) . $manager->getTable());
		$output->writeln('Migrations Namespace: ' . $s(11) . trim($manager->getNamespace(), '\\'));
		$output->writeln('Migrations Directory: ' . $s(11) . $manager->getPath());
		$currentVersion = $manager->getLastMigrationRan(true);
		if ($currentVersion) {
			$date = Datetime::createFromFormat('YmdHis', $currentVersion);
			if ($date)
				$output->writeln('Current Version: ' . $s(16) . $date->format('Y-m-d H:i:s') . ' (<fg=cyan>' . $currentVersion . '</>)');
			else
				$output->writeln('Current Version: ' . $s(16) . '<fg=cyan>' . $currentVersion . '</>');
		}
		else {
			$output->writeln('Current Version: ' . $s(16) . 'n/a');
		}
		$migrationConfig = $manager->getMigrationConfig();
		if (sizeof($migrationConfig)) {
			$currentVersionTmp = array_values($migrationConfig);
			$date = Datetime::createFromFormat('YmdHis', $currentVersionTmp[0]['key']);
			if ($date)
				$output->writeln('Lastest Version: ' . $s(16) . $date->format('Y-m-d H:i:s') . ' (<fg=cyan>' . $currentVersionTmp[0]['key'] . '</>)');
			else
				$output->writeln('Lastest Version: ' . $s(16) . '<fg=cyan>' . $currentVersionTmp[0]['key'] . '</>');
		}
		else {
			$output->writeln('Latest Version: ' . $s(17) . 'n/a');
		}
		$output->writeln('Executed Migrations:  ' . $s(11) . $manager->getExecutedMigrationCount());
		$output->writeln('Executed Unavailable Migrations: ' . $manager->getExecutedUnavailableMigrationCount());
		$output->writeln('Total Migrations: ' . $s(15) . sizeof($migrationConfig));
		$migrateUpCount = $manager->migrationUpCount();
		$output->writeln('New Migrations: ' . $s(17) . $migrateUpCount['migrations']);
		$output->writeln('');
	}
}
