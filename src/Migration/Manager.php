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

namespace Lynk\Component\Migration;

use Exception;
use PDO;
use Lynk\Component\Connection\ConnectionWrapped;

/**
 * Migrations manager class. Responsible for performing migrations.
 */
class Manager {

	/**
	 * @var ConnectionWrapped Database connection opbject.
	 */
	private $connection;

	/**
	 * @var string Table name.
	 */
	private $tableName;

	/**
	 * @var string Migrations namespace.
	 */
	private $namespace;

	/**
	 * @var string Migration file path.
	 */
	private $path;

	/**
	 * @var string Project root directory.
	 */
	private $root;

	/**
	 * @var bool Whether or not the migrations system has been Initialized.
	 */
	private $migrationsInited;

	/**
	 * @var Array Migrations configuration/list.
	 */
	private $migrationConfig;

	/**
	 * @param PDO $pdo PDO connection class.
	 * @param string $table The table name.
	 * @param string $namespace Migration class namespace.
	 * @param string $path Migration file path.
	 * @param string $root Project root directory.
	 */
	public function __construct(PDO $pdo, $table, $namespace, $path, $root) {
		$this->connection = new ConnectionWrapped($pdo);
		$this->tableName = $table;
		$namespace = trim($namespace, '\\');
		if ($namespace != '') {
			$namespace = "\\{$namespace}";
		}
		else {
			$namespace = "";
		}
		$this->namespace = $namespace;
		$this->path = $path;
		$this->root = $root;
		$this->migrationsInited = null;
		$this->migrationConfig = null;
	}

	/**
	 * Initialize migrations system. Create required table.
	 */
	protected function initMigrations() {
		$connection = $this->getConnection();
		if ($this->migrationsInited !== null) {
			return $this->migrationsInited;
		}
		try {
			$result = $connection->run("SELECT 1 FROM {$this->tableName} LIMIT 1");
			if ($result['result']) {
				$this->migrationsInited = true;
				return true;
			}
			throw new Exception($result['errorMessage']);
		}
		catch (Exception $exception) {
			$driver = $connection->getDriver();
			$query = null;
			if ($driver == 'mysql') {
				$query = "
					CREATE TABLE `{$this->tableName}` (
						`version` BIGINT NOT NULL,
						PRIMARY KEY (`version`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				";
			}
			else if ($driver == 'sqlite') {
				$query = "
					CREATE TABLE \"{$this->tableName}\" (
						\"version\" INTEGER PRIMARY KEY NOT NULL
					);
				";
			}
			$result = $connection->run($query);
			if ($result['result']) {
				$this->migrationsInited = true;
				return true;
			}
			else {
				$this->migrationsInited = false;
				return false;
			}
		}
		$this->migrationsInited = false;
		return false;
	}

	/**
	 * Return the last migration ran.
	 * 
	 * @param bool $returnVersion Set to true to return version, otherwise array representation of migration is returned.
	 * 
	 * @return mixed Array representation of migration or version string.
	 */
	public function getLastMigrationRan($returnVersion = true) {
		if ($this->initMigrations()) {
			$connection = $this->getConnection();
			$result = $connection->run("SELECT * FROM {$this->tableName} ORDER BY version DESC LIMIT 1");
			if ($result['result'] && $result['rowCount'] > 0) {
				$migrations = $this->getMigrationConfig();
				if ($returnVersion)
					return $result['data'][0]['version'];
				else if (isset($migrations[$result['data'][0]['version']])) {
					return $migrations[$result['data'][0]['version']];
				}
			}
		}
		return null;
	}

	/**
	 * Check if migration exists.
	 * 
	 * @param string $version Version to check for.
	 * 
	 * @return bool True if migration exists, false otherwise.
	 */
	public function migrationExists($version) {
		$migrations = $this->getMigrationConfig();
		return array_key_exists($version, $migrations);
	}

	/**
	 * Migrate up or down to a specific migration. This method runs all miogrations on the way.
	 * 
	 * @param mixed $instance Migration instance or version.
	 * @param bool $up Migrate up if true, down if false.
	 * 
	 * @return Array Migration result counts.
	 */
	public function migrate($instance, $up = true) {
		if (is_string($instance)) {
			$config = $this->getMigrationConfig();
			$instance = isset($config[$instance]) ?
				$config[$instance]['instance']
				: null;

		}
		$call = $up ? 'doUp' : 'doDown';
		return $instance
			? $instance->{$call}()->runStack(function($type, $result, $item) {
				$ucfType = ucfirst($type);
				echo "{$ucfType}: " . ($result ? 'success' : 'fail') . "\n";
				echo "\n\t{$item}\n\n";
			})
			: null;
	}

	/**
	 * Execute a specific migration up or down. This method only runs the specified migration.
	 * 
	 * @param mixed $instance Migration instance or version.
	 * @param bool $up Migrate up if true, down if false.
	 * 
	 * @return mixed Migration result or null if failure.
	 */
	public function execute($instance, $up = true) {
		if (is_string($instance)) {
			$config = $this->getMigrationConfig();
			$instance = isset($config[$instance]) ?
				$config[$instance]['instance']
				: null;

		}
		if (!$instance) {
			return null;
		}
		if ($up && $this->hasVersion($instance->getVersion())) {
			return 1;
		}
		$migrateResult = $this->migrate($instance, $up);
		if ($migrateResult) {
			if ($up) {
				$this->addVersion($instance->getVersion());
			}
			else {
				$this->removeVersion($instance->getVersion());
			}
			return $migrateResult;
		}
		else
			return null;
	}

	/**
	 * Migrate up.
	 * 
	 * @param string $version Optional. Migration version.
	 * 
	 * @return Array Migration results.
	 */
	public function migrateUp($version = null) {
		$migrations = $this->migrateUpMigrations($version);
		$successful = $failed = $successfulQueries = $failedQueries = $successfulActions = $failedActions = Array();
		foreach ($migrations as $key => $migration) {
			if (sizeof($failed)) {
				continue;
			}
			echo "[[ Migration {$key} ]]\n\n";
			$instance = $migration['instance'];
			$instanceResult = $this->execute($instance, true);
			if ($instanceResult) {
				list(
					$runSuccessful, $runSuccessfulQueries, $runSuccessfulActions, $runFailed, $runFailedQueries, $runFailedActions
				) = array_values($instanceResult);
				$successful = array_merge($successful, $runSuccessful);
				$successfulQueries = array_merge($successfulQueries, $runSuccessfulQueries);
				$successfulActions = array_merge($successfulActions, $runSuccessfulActions);
				$failed = array_merge($failed, $runFailed);
				$failedQueries = array_merge($failedQueries, $runFailedQueries);
				$failedActions = array_merge($failedActions, $runFailedActions);
				if (sizeof($runFailed)) {
					continue;
				}
			}
		}
		return Array(
			'successful' => $successful,
			'successfulQueries' => $successfulQueries,
			'successfulActions' => $successfulActions,
			'failed' => $failed,
			'failedQueries' => $failedQueries,
			'failedActions' => $failedActions
		);
	}

	/**
	 * Migrate down.
	 * 
	 * @param string $version Optional. Migration version.
	 * 
	 * @return Array Migration results.
	 */
	public function migrateDown($version = null) {
		$migrations = $this->migrateDownMigrations($version);
		$successful = $failed = $successfulQueries = $failedQueries = $successfulActions = $failedActions = Array();
		foreach ($migrations as $key => $migration) {
			if (sizeof($failed)) {
				continue;
			}
			echo "[[ Migration {$key} ]]\n\n";
			$instance = $migration['instance'];
			$instanceResult = $this->execute($instance, false);
			if ($instanceResult) {
				list(
					$runSuccessful, $runSuccessfulQueries, $runSuccessfulActions, $runFailed, $runFailedQueries, $runFailedActions
				) = array_values($instanceResult);
				$successful = array_merge($successful, $runSuccessful);
				$successfulQueries = array_merge($successfulQueries, $runSuccessfulQueries);
				$successfulActions = array_merge($successfulActions, $runSuccessfulActions);
				$failed = array_merge($failed, $runFailed);
				$failedQueries = array_merge($failedQueries, $runFailedQueries);
				$failedActions = array_merge($failedActions, $runFailedActions);
				if (sizeof($runFailed)) {
					continue;
				}
			}
		}
		return Array(
			'successful' => $successful,
			'successfulQueries' => $successfulQueries,
			'successfulActions' => $successfulActions,
			'failed' => $failed,
			'failedQueries' => $failedQueries,
			'failedActions' => $failedActions
		);
	}

	/**
	 * Get migrations to run up to a certain migration or all remaining migrations.
	 * 
	 * @param string $to Optional. Migration to go up to.
	 * 
	 * @return Array Migrations config array.
	 */
	public function migrateUpMigrations($to = null) {
		if ($this->initMigrations()) {
			$lastMigration = $this->getLastMigrationRan(true);
			$migrationConfig = $this->getMigrationConfig();
			$migrationsToRun = array();
			if ($lastMigration || $to) {
				foreach ($migrationConfig as $migrationKey => $migration) {
					if ($migrationKey == $lastMigration) {
						break;
					}
					if ($to && (int)$migrationKey > (int)$to) {
						continue;
					}
					if (!$this->hasVersion($migrationKey)) {
						$migrationsToRun[$migrationKey] = $migration;
					}
				}
			}
			else {
				$migrationsToRun = $migrationConfig;
			}
			$migrationsToRun = array_reverse($migrationsToRun, true);
			return $migrationsToRun;
		}
		return Array();
	}

	/**
	 * Get migrations to run down to a certain migration or back to beginning.
	 * 
	 * @param string $to Optional. Migration to go down to.
	 * 
	 * @return Array Migrations config array.
	 */
	public function migrateDownMigrations($to = null) {
		if ($this->initMigrations()) {
			$lastMigration = $this->getLastMigrationRan(true);
			$migrationConfig = $this->getMigrationConfig();
			$migrationsToRun = array();
			$migrationConfig = array_reverse($migrationConfig, true);
			if ($lastMigration) {
				foreach ($migrationConfig as $migrationKey => $migration) {
					if ((int)$migrationKey <= (int)$lastMigration && 
						(!$to || (int)$migrationKey >= (int)$to)
					) {
						if ($this->hasVersion($migrationKey)) {
							$migrationsToRun[$migrationKey] = $migration;
						}
					}
					if ($migrationKey == $lastMigration) {
						break;
					}
				}
				$migrationsToRun = array_reverse($migrationsToRun, true);
				return $migrationsToRun;
			}
		}
		return Array();
	}

	/**
	 * Get total query and action migrations count.
	 * 
	 * @param mixed $instance Migration instance or version.
	 * @param bool $up Optional. Migrate up if true, down if false.
	 * 
	 * @return Array Query and action count values.
	 */
	public function migrationCount($instance, $up = true) {
		if (is_string($instance)) {
			$config = $this->getMigrationConfig();
			$instance = isset($config[$instance]) ?
				$config[$instance]['instance']
				: null;
		}
		$count = Array('queries' => 0, 'actions' => 0);
		$call = $up ? 'doUp' : 'doDown';
		if ($instance) {
			$instance->{$call}();
			$count['queries'] = $instance->getQueryCount();
			$count['actions'] = $instance->getActionCount();
		}
		return $count;
	}

	/**
	 * Total migration up count.
	 * 
	 * @return Array Migration, query and action counts.
	 */
	public function totalMigrationUpCount() {
		$migrations = $this->getMigrationConfig();
		$queryCount = $actionCount = 0;
		foreach ($migrations as $migration) {
			$count = $this->migrationCount($migration['instance'], true);
			$queryCount += $count['queries'];
			$actionCount += $count['actions'];
		}
		return Array(
			'migrations' => sizeof($migrations),
			'queries' => $queryCount,
			'actions' => $actionCount
		);
	}

	/**
	 * Migration up counts to specific migration.
	 * 
	 * @param string $to Optional. Migration version
	 * 
	 * @return Array Migration, query and action counts.
	 */
	public function migrationUpCount($to = null) {
		$migrations = $this->migrateUpMigrations($to);
		$queryCount = $actionCount = 0;
		foreach ($migrations as $migration) {
			$count = $this->migrationCount($migration['instance'], true);
			$queryCount += $count['queries'];
			$actionCount += $count['actions'];
		}
		return Array(
			'migrations' => sizeof($migrations),
			'queries' => $queryCount,
			'actions' => $actionCount
		);
	}

	/**
	 * Migration up counts to specific migration.
	 * 
	 * @param string $to Optional. Migration version
	 * 
	 * @return Array Migration, query and action counts.
	 */
	public function migrationDownCount($to = null) {
		$migrations = $this->migrateDownMigrations($to);
		$queryCount = $actionCount = 0;
		foreach ($migrations as $migration) {
			$count = $this->migrationCount($migration['instance'], false);
			$queryCount += $count['queries'];
			$actionCount += $count['actions'];
		}
		return Array(
			'migrations' => sizeof($migrations),
			'queries' => $queryCount,
			'actions' => $actionCount
		);
	}

	/**
	 * Get count of migrations ran that no longer have a migration file.
	 * 
	 * @return int Unavailable migration counts.
	 */
	public function getExecutedUnavailableMigrationCount() {
		if ($this->initMigrations()) {
			$connection = $this->getConnection();
			$migrations = $this->getMigrationConfig();
			$in = '';
			$params = Array();
			$count = -1;
			foreach ($migrations as $key => $migration) {
				$count++;
				$in .= ":v{$count},";
				$params["v{$count}"] = $key;
			}
			$in = trim($in, ',');
			$result = $connection->run(
				"SELECT DISTINCT COUNT(version) FROM {$this->tableName} WHERE version NOT IN (
					SELECT version FROM {$this->tableName} WHERE version IN ($in)
				)",
				$params
			);
			return $result['data'][0]['COUNT(version)'];
		}
		return 0;
	}

	/**
	 * Get executed migration counts.
	 * 
	 * @return int count of migrations ran.
	 */
	public function getExecutedMigrationCount() {
		if ($this->initMigrations()) {
			$connection = $this->getConnection();
			$result = $connection->run("SELECT DISTINCT COUNT(version) FROM {$this->tableName}");
			return $result['data'][0]['COUNT(version)'];
		}
		return 0;
	}

	/**
	 * Get migration count.
	 * 
	 * @return int Total count of migrations.
	 */
	public function getTotalMigrations() {
		return sizeof($this->getMigrationConfig());
	}

	/**
	 * Add version to migration table.
	 * 
	 * @param string $version Migration version.
	 * 
	 * @return bool True if version added or already exists, false otherwise.
	 */
	public function addVersion($version) {
		if ($this->initMigrations()) {
			$connection = $this->getConnection();
			$result = $connection->run(
				"SELECT * FROM {$this->tableName} WHERE version=:version",
				Array('version' => $version)
			);
			if (!sizeof($result['data'])) {
				$result = $connection->run(
					"INSERT INTO {$this->tableName}(version) VALUES (:version)",
					Array('version' => $version)
				);
				return $result['result'];
			}
			return $result['result'];
		}
		return false;
	}

	/**
	 * Remove version from migration table.
	 * 
	 * @param string $version Migration version.
	 * 
	 * @return bool True if removed, false otherwise.
	 */
	public function removeVersion($version) {
		if ($this->initMigrations()) {
			$connection = $this->getConnection();
			$result = $connection->run(
				"DELETE FROM {$this->tableName} WHERE version=:version",
				Array('version' => $version)
			);
			return $result['result'];
		}
		return false;
	}

	/**
	 * Check if version exists in table.
	 * 
	 * @param string $version Migration version.
	 * 
	 * @return bool True if version exists, false if not.
	 */
	public function hasVersion($version) {
		if ($this->initMigrations()) {
			$connection = $this->getConnection();
			$result = $connection->run(
				"SELECT * FROM {$this->tableName} WHERE version=:version",
				Array('version' => $version)
			);
			if ($result['result'] && $result['rowCount']) {
				return true;
			}
			else {
				return false;
			}
		}
	}

	/**
	 * Generate migration class.
	 * 
	 * @param string $version Migration version.
	 * 
	 * @return string Migration class.
	 */
	public function generateClass($version) {
		return str_replace(Array(
			'{{namespace}}',
			'{{extend.namespace}}',
			'{{extend.class}}',
			'{{version}}',
			'{{datetime}}'
		), Array(
			trim($this->namespace, '\\'),
			'use Lynk\\Component\\Migration\\Migration;',
			'Migration',
			$version,
			date('F j, Y h:i:s A')
		), file_get_contents(__DIR__ . '/Templates/NewMigrationTemplate.php'));
	}

	/**
	 * Get migration configuration.
	 * 
	 * @return Array Migration config in an array.
	 */
	public function getMigrationConfig() {
		if ($this->migrationConfig === null) {
			$migrationFiles = glob("{$this->path}/Migration*.php");
			$migrations = Array();
			$migrationKeys = Array();
			foreach ($migrationFiles as $file) {
				$migrationKey = null;
				preg_match('/Migration([\d]+)\.php$/', $file, $migrationClass);
				if (isset($migrationClass[1])) {
					$migrationKey = $migrationClass[1];
					$migrationClass = "{$this->namespace}\\Migration{$migrationKey}";
				}
				if ($migrationKey) {
					$migrationKeys[] = $migrationKey;
					$migrations[(int)$migrationKey] = Array(
						'file' => $file,
						'class' => $migrationClass,
						'key' => $migrationKey
					);
				}
			}
			usort($migrationKeys, function($a, $b) {
				if ((int)$a == (int)$b) {
					return 0;
				}
				return (int)$a < (int)$b ? 1 : -1;
			});
			$final = Array();
			foreach ($migrationKeys as $key) {
				if (isset($migrations[$key])) {
					$final[(int)$key] = $migrations[$key];
					if (!class_exists($final[(int)$key]['class']))
						include $final[(int)$key]['file'];
					$final[(int)$key]['instance'] = new $final[(int)$key]['class']($this->getConnection(), $this->root);
				}
			}
			$this->migrationConfig = $final;
		}
		return $this->migrationConfig;
	}

	/**
	 * Get migration namespace.
	 * 
	 * @return string Migrations namespace.
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * Get migrations file path.
	 * 
	 * @param string Migration file path.
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get migration table name.
	 * 
	 * @return string Table name.
	 */
	public function getTable() {
		return $this->tableName;
	}

	/**
	 * Get database connection.
	 * 
	 * @return ConnectionWrapped Database connection opbject.
	 */
	public function getConnection() {
		return $this->connection;
	}
}