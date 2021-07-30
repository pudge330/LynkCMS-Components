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

use LynkCMS\Component\Migration\Manager;
use Symfony\Component\Console\Application;

/**
 * Load command into application.
 */
class CommandLoader {

	/**
	 * Load commands.
	 * 
	 * @param Symfony\Component\Console\Application $app Console application.
	 * @param Manager $manager Migration manager instance.
	 * @param string $prefix Optional. Command name prefix.
	 */
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