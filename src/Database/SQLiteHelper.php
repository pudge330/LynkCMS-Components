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
 * @subpackage Database
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Database;

use Exception;
use PDO;
use Lynk\Component\Connection;

/**
 * SQLite helper class.
 */
class SQLiteHelper {

	/**
	 * @var Connection|ConnectionWrapped Database connection.
	 */
	protected $connection;

	/**
	 * @param PDO|Connection|ConnectionWrapped $connection Database connection.
	 */
	public function __construct($connection) {
		$isPDO = ($connection instanceof PDO);
		$isConnection = ($connection instanceof Connection\Connection);
		$isConnectionWrapped = ($connection instanceof Connection\ConnectionWrapped);
		if (!($isPDO || $isConnection || $isConnectionWrapped)) {
			throw new Exception('SQLiteHelper: $connection must be a instance of PDO, Lynk\Component\Connection\Connection, or Lynk\Component\Connection\ConnectionWrapped.');
		}
		if ($isPDO) {
			$connection = new Connection\ConnectionWrapped($connection);
		}
		$this->connection = $connection;
	}

	/**
	 * Rebuild table.
	 * SQLite doesn't allow dropping columns and certain other features like adding a column after a specific one.
	 * This function will assist in rebuilding a new table and transfering data over.
	 * 
	 * @param string $tableName Table name.
	 * @param string $tmpTableName Temporary table name.
	 * @param string $tmpTable Temporary table sql code.
	 * @param string $columns Columns, comma separated.
	 * @param string $primaryKey Optional. Primary key column name. Only needed to set next autoincrement value.
	 */
	public function rebuildTable($tableName, $tmpTableName, $tmpTable, $columns, $primaryKey = null) {
		$this->connection->run("PRAGMA foreign_keys=off");
		$this->connection->run("BEGIN TRANSACTION");
		$this->connection->run($tmpTable);
		$this->connection->run("INSERT INTO {$tmpTableName}({$columns}) SELECT {$columns} FROM {$tableName}");
		$this->connection->run("DROP TABLE {$tableName}");
		$this->connection->run("ALTER TABLE {$tmpTableName} RENAME TO {$tableName");
		$this->connection->run("COMMIT");
		$this->connection->run("PRAGMA foreign_keys=on");
		if ($primaryKey) {
			$result = $this->connection->run("SELECT {$primaryKey} FROM {$tableName} ORDER BY {$primaryKey} DESC");
			$nextUid = $result['data'][0][$primaryKey] + 1;
			$this->connection->run("INSERT INTO sqlite_sequence VALUES ('{$tableName}', '{$nextUid}')");
		}
	}

	/**
	 * Rename table.
	 * This function will assist in renaming a table and transfering data over.
	 * 
	 * @param string $tableName Table name.
	 * @param string $newTableName Temporary table name.
	 * @param string $newTable Temporary table sql code.
	 * @param string $columns Columns, comma separated.
	 * @param string $primaryKey Optional. Primary key column name. Only needed to set next autoincrement value.
	 */
	public function renameTable($tableName, $newTableName, $newTable, $columns, $primaryKey = null) {
		$this->connection->run("PRAGMA foreign_keys=off");
		$this->connection->run("BEGIN TRANSACTION");
		$this->connection->run($newTable);
		$this->connection->run("INSERT INTO {$newTableName}({$columns}) SELECT {$columns} FROM {$tableName}");
		$this->connection->run("DROP TABLE {$tableName}");
		$this->connection->run("COMMIT");
		$this->connection->run("PRAGMA foreign_keys=on");
		if ($primaryKey) {
			$result = $this->connection->run("SELECT {$primaryKey} FROM {$newTable} ORDER BY {$primaryKey} DESC");
			$nextUid = $result['data'][0][$primaryKey] + 1;
			$this->connection->run("INSERT INTO sqlite_sequence VALUES ('{$newTable}', '{$nextUid}')");
		}
	}
}