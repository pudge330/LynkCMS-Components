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
 * @subpackage Logger
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Log\Listener;

use Closure;
use Exception;
use PDO;
use LynkCMS\Component\Connection;
use LynkCMS\Component\Log\Event\LogEvent;

/**
 * Generic listener class. Calls a closure.
 */
class DatabaseListener extends AbstractListener {

	/**
	 * @var Connection|ConnectionWrapped Database connection.
	 */
	protected $connection;

	/**
	 * @var string Table name.
	 */
	protected $tableName;

	/**
	 * @var Closure Data mapping closure.
	 */
	protected $dataMapping;

	/**
	 * @param PDO|Connection|ConnectionWrapped $connection Database connection.
	 * @param string $table Table name.
	 * @param Closure $dataMapping Optional. Closure to modify data for insert command.
	 * 
	 * @throws Exception
	 */
	public function __construct($connection, $table, Closure $dataMapping = null) {
		parent::__construct();
		$isPDO = ($connection instanceof PDO);
		$isConnection = ($connection instanceof Connection\Connection);
		$isConnectionWrapped = ($connection instanceof Connection\ConnectionWrapped);
		if (!($isPDO || $isConnection || $isConnectionWrapped)) {
			throw new Exception('DatabaseListener: $connection must be a instance of PDO, LynkCMS\Component\Connection\Connection, or LynkCMS\Component\Connection\ConnectionWrapped.');
		}
		if ($isPDO) {
			$connection = new Connection\ConnectionWrapped($connection);
		}
		$this->connection = $connection;
		$this->table = $table;
		$this->dataMapping = $dataMapping;
	}

	/**
	 * Handle the event.
	 * 
	 * @param LogEvent The log event.
	 * 
	 * @return mixed Return value of the callback.
	 */
	public function handle(LogEvent $event) {
		$data = $this->mapEventData($event);
		if ($this->dataMapping) {
			$data = ($this->dataMapping)($event, $data);
		}
		$columns = array_keys($data);
		$values = ':' . implode(',:', $columns);
		$columns = implode(',', $columns);
		$query = "INSERT INTO {$this->table}({$columns}) VALUES ($values)";
		$this->connection->run($query, $data);
	}

	/**
	 * Map event data to table columns. Return an associative array of column data pairs.
	 * 
	 * @param LogEvent The log event.
	 * 
	 * @return mixed Return value of the callback.
	 */
	protected function mapEventData(LogEvent $event) {
		$data = parent::mapEventData($event);
		$data['timestamp'] = date('YmdHis');
		return $data;
	}
}