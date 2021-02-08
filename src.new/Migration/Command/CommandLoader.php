<?php
namespace LynkCMS\Component\Migration\Command;

use LynkCMS\Component\Migration\Manager;
use Symfony\Component\Console\Application;

class CommandLoader {
	public static function loadCommands(Application $app, Manager $manager, $prefix = '') {
		$app->addCommands(Array(
			new MigrationExecuteCommand($manager, $prefix),
			new MigrationGenerateCommand($manager, $prefix),
			new MigrationMigrateCommand($manager, $prefix),
			new MigrationStatusCommand($manager, $prefix),
			new MigrationVersionCommand($manager, $prefix),
			new MigrationTestCommand($manager, $prefix)
		));
	}
}