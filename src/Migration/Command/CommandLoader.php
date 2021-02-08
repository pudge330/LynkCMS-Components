<?php
namespace BGStudios\Component\Migration\Command;

use BGStudios\Component\Migration\Manager;
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