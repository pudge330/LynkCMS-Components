<?php
namespace LynkCMS\Component\Migration;

use Exception;
use PDO;
use LynkCMS\Component\Connection\ConnectionWrapped;

class Manager {
	private $connection;
	private $tableName;
	private $namespace;
	private $path;
	private $root;
	private $migrationsInited;
	private $migrationConfig;

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

	public function migrationExists($version) {
		$migrations = $this->getMigrationConfig();
		return array_key_exists($version, $migrations);
	}

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

	public function getExecutedMigrationCount() {
		if ($this->initMigrations()) {
			$connection = $this->getConnection();
			$result = $connection->run("SELECT DISTINCT COUNT(version) FROM {$this->tableName}");
			return $result['data'][0]['COUNT(version)'];
		}
		return 0;
	}

	public function getTotalMigrations() {
		return sizeof($this->getMigrationConfig());
	}

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

	public function generateClass($version) {
		return str_replace(Array(
			'{{namespace}}',
			'{{extend.namespace}}',
			'{{extend.class}}',
			'{{version}}'
		), Array(
			trim($this->namespace, '\\'),
			'use LynkCMS\\Component\\Migration\\Migration;',
			'Migration',
			$version
		), file_get_contents(__DIR__ . '/Templates/NewMigrationTemplate.php'));
	}

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

	public function getNamespace() {
		return $this->namespace;
	}

	public function getPath() {
		return $this->path;
	}

	public function getTable() {
		return $this->tableName;
	}

	public function getConnection() {
		return $this->connection;
	}
}