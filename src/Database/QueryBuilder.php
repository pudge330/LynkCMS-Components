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
use Lynk\Component\Connection\Connection;
use Lynk\Component\Connection\ConnectionWrap;

/**
 * Query builder class. Used to generate SELECT, UPDATE, INSERT and DELETE queries.
 */
class QueryBuilder {

	/**
	 * @var string Delete query identifier.
	 */
	const DELETE = 'delete';

	/**
	 * @var string Insert query indentifier.
	 */
	const INSERT = 'insert';

	/**
	 * @var string Select query identifier.
	 */
	const SELECT = 'select';

	/**
	 * @var string Update query identifier.
	 */
	const UPDATE = 'update';

	/**
	 * @var string Upsert query identifier.
	 */
	const UPSERT = 'upsert';

	/**
	 * @var string Dirty state identifier.
	 */
	const STATE_DIRTY = 'dirty';

	/**
	 * @var string Clean state identifier.
	 */
	const STATE_CLEAN = 'clean';

	/**
	 * @var Connection|ConnectionWrapped Database connection instance.
	 */
	protected $connection;

	/**
	 * @var Array Query segments.
	 */
	protected $querySegments;

	/**
	 * @var int Parameter counter.
	 */
	protected $counter;

	/**
	 * @param Connection|ConnectionWrapped Database connection instance.
	 */
	public function __construct($connection) {
		if (!($connection instanceof PDO || $connection instanceof ConnectionWrap)) {
			throw new Exception('QueryBuilder: First argument of constructor must be instance of PDO or ConnectionWrap.');
		}
		$this->connection = $connection instanceof Connection || $connection instanceof ConnectionWrap
			? $connection
			: new ConnectionWrap($connection);
		$this->querySegments = $this->defaultSegments();
		$this->counter = 0;
	}

	/**
	 * Get current state of query builder.
	 * Clean means no query has ben started, dirty means that it has.
	 * 
	 * @return string Query builder state.
	 */
	public function getState() {
		$defaults = $this->defaultSegments();
		if ($this->querySegments === $defaults)
			return self::STATE_CLEAN;
		else
			return self::STATE_DIRTY;
	}

	/**
	 * Get query type.
	 * 
	 * @return string Query type.
	 */
	public function getType() {
		if ($this->querySegments['type'] == self::DELETE)
			return self::DELETE;
		else if ($this->querySegments['type'] == self::INSERT)
			return self::INSERT;
		else if ($this->querySegments['type'] == self::SELECT)
			return self::SELECT;
		else if ($this->querySegments['type'] == self::UPDATE)
			return self::UPDATE;
		else
			return 'unset';
	}

	/**
	 * Get default query segments.
	 * 
	 * @return Array Query segments.
	 */
	protected function defaultSegments() {
		return Array(
			'type' => null
			,'select' => Array() //-select
			,'insert' => Array() //-insert
			,'delete' => Array() //-delete
			,'update' => Array() //-update
			,'from' => Array() //-select
			,'joins' => Array() //-select,delete,update
			,'where' => null //-select,delete,update
			,'extraWhere' => Array() //-select,delete,update
			,'order' => Array() //-select
			,'offset' => null //-select
			,'limit' => null //-select
			,'values' => Array() //-insert
			,'set' => Array() //-update
			,'groupBy' => Array() //-select
			,'having' => null //-select
			,'extraHaving' => Array() //-select
			,'distinct' => null //-select
			,'parameters' => Array() //-select,delete,insert,update
		);
	}

	/**
	 * Reset query segment variables.
	 */
	protected function resetVariables() {
		$this->querySegments = $this->defaultSegments();
	}

	/**
	 * Get connection object.
	 * 
	 * @return Connection|ConnectionWrapped Database connection.
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * Get main table alias.
	 * 
	 * @return string Table alias, null if not set.
	 */
	public function getRootAlias() {
		if ($this->querySegments['type'] == self::SELECT)
			return isset($this->querySegments['from'][1]) ? $this->querySegments['from'][1] : null;
		else if ($this->querySegments['type'] == self::DELETE)
			return isset($this->querySegments['delete'][1]) ? $this->querySegments['delete'][1] : null;
		else if ($this->querySegments['type'] == self::UPDATE)
			return isset($this->querySegments['update'][1]) ? $this->querySegments['update'][1] : null;
	}

	/**
	 * Get list of all aliases. Includes joins.
	 * 
	 * @return Array All table aliases.
	 */
	public function getAllAliases() {
		$aliases = Array();
		$tmp = $this->getRootAlias();
		if ($tmp)
			$aliases[] = $tmp;
		foreach ($this->querySegments['joins'] as $join) {
			$tmp = isset($join['table'][1]) ? $join['table'][1] : null;
			if ($tmp)
				$aliases[] = $tmp;
		}
		return $aliases;
	}

	/**
	 * Get main table.
	 * 
	 * @return string Table name, null if not set.
	 */
	public function getRootTable() {
		if ($this->querySegments['type'] == self::SELECT)
			return isset($this->querySegments['from'][0]) ? $this->querySegments['from'][0] : null;
		else if ($this->querySegments['type'] == self::DELETE)
			return isset($this->querySegments['delete'][0]) ? $this->querySegments['delete'][0] : null;
		else if ($this->querySegments['type'] == self::UPDATE)
			return isset($this->querySegments['update'][0]) ? $this->querySegments['update'][0] : null;
	}

	/**
	 * Run query.
	 * 
	 * @param Array $data Optional. Parameter data.
	 * @param bool $delayFetch Optional. Whether or not to delay data fetching. Useful for large datasets.
	 */
	public function query($data = null, $delayFetch = false) {
		$query = $this->buildQuery();
		if (sizeof($this->querySegments['parameters'])) {
			$data = is_array($data) ? array_merge($this->querySegments['parameters'], $data) : $this->querySegments['parameters'];
		}
		$res = $this->connection->run($query, $data, $delayFetch);
		return $res;
	}

	/**
	 * Build query string from segments.
	 * 
	 * @return string Constructed query. 
	 */
	protected function buildQuery() {
		$segments = $this->querySegments;
		$isDelete = ($segments['type'] == self::DELETE);
		$isInsert = ($segments['type'] == self::INSERT);
		$isSelect = ($segments['type'] == self::SELECT);
		$isUpdate = ($segments['type'] == self::UPDATE);
		$query = '';
		if ($isSelect) {
			if (is_array($segments['select'])) {
				$segments['select'] = implode(',', $segments['select']);
			}
			$segments['select'] = explode(',', $segments['select']);
			foreach ($segments['select'] as $key => $value) {
				$value = trim($value);
				$isSqlFnSelect = is_string($value)
					? preg_match('/\s*(avg|count|max|min|sum)\s*\(\s*[\w\*]+\s*\)\s*/', strtolower($value))
					: false;
				if (strpos($value, '.') === false && !preg_match('/ as /', $value) && !$isSqlFnSelect) {
					$value = "{$segments['from'][1]}.{$value}";
					$segments['select'][$key] = $value;
				}

			}
			$segments['select'] = implode(',', $segments['select']);
			$distinct = $segments['distinct'] ? ' DISTINCT' : '';
			$query .= "SELECT{$distinct} {$segments['select']} FROM {$segments['from'][0]} {$segments['from'][1]} ";
		}
		else if ($isInsert) {
			$insert = $segments['insert'];
			if ($insert[1] && strpos($insert[1], '(')  === false) {
				$insert[1] = "({$insert[1]})";
			}
			$query .= "INSERT INTO {$insert[0]}{$insert[1]} ";
			$query .= sizeof($segments['values']) ? 'VALUES ' : '';
			foreach ($segments['values'] as $value) {
				if ($value && strpos($value, '(') === false) {
					$value = "({$value})";
				}
				$query .= "{$value}, ";
			}
			$query = rtrim($query, ', ') . ' ';
		}
		else if ($isDelete) {
			$query = "DELETE FROM {$segments['delete'][0]} ";
		}
		else if ($isUpdate) {
			$query = "UPDATE {$segments['update'][0]} as {$segments['update'][1]} ";
			$query .= sizeof($segments['set']) ? 'SET ' : '';
			foreach ($segments['set'] as $setKey => $setValue) {
				$query .= "{$setKey}={$setValue}, ";
			}
			$query = rtrim($query, ', ') . ' ';
		}
		if (!$isInsert) {
			//--process joins
			foreach ($segments['joins'] as $join) {
				$table = $join['table'][0];
				$tableAlias = isset($join['table'][1]) ? $join['table'][1] : null;
				$what = $join['what'];
				$rootAlias = null;
				if ($isSelect) $rootAlias = $segments['from'][1];
				else if ($isDelete) $rootAlias = $segments['delete'][0];
				else if ($isUpdate) $rootAlias = $segments['update'][0];
				if (strpos($what, '.') === false && $rootAlias)
					$what = "{$rootAlias}.{$what}";
				$on = $join['on'];
				if ($tableAlias && strpos($on, '.') === false)
					$on = "{$tableAlias}.{$on}";
				else if (strpos($on, '.') === false)
					$on = "{$table}.{$on}";
				$table = $tableAlias ? "{$table} {$tableAlias}" : "{$table}";
				$type = $join['type'] != 'join' ? strtoupper($join['type']) . ' ' : '';
				$query .= "{$type}JOIN {$table} ON {$what}={$on} ";
			}
			//--where
			if ($segments['where']) {
				$where = sizeof($segments['extraWhere']) && !preg_match('/^\((.*)\)$/', $segments['where']) ? "({$segments['where']})" : $segments['where'];
				$query .= "WHERE {$where} ";
				foreach ($segments['extraWhere'] as $where) {
					$type = strtoupper($where['type']);
					$tmpWhere = !preg_match('/^\((.*)\)$/', $where['where']) ? "({$where['where']})" : $where['where'];
					$query .= "{$type} {$tmpWhere} ";
				}
			}
		}
		//--groupBy having orderBy limit/offset
		if ($isSelect) {
			if (sizeof($segments['groupBy'])) {
				$groupBy = implode(',', $segments['groupBy']);
				$query .= "GROUP BY {$groupBy} ";
			}
			if ($segments['having']) {
				$query .= "HAVING {$segments['having']} ";
				foreach ($segments['extraHaving'] as $having) {
					$type = strtoupper($having['type']);
					$query .= "{$type} {$having['having']} ";
				}
			}
			if (sizeof($segments['order'])) {
				$query .= "ORDER BY ";
				foreach ($segments['order'] as $order) {
					$query .= "{$order[0]} {$order[1]}, ";
				}
				$query = rtrim($query, ', ') . ' ';
			}
			if ($segments['limit'] !== null) {
				$query .= "LIMIT {$segments['limit']} ";
			}
			if ($segments['offset'] !== null) {
				$query .= "OFFSET {$segments['offset']}";
			}
		}
		return trim($query);
	}

	/**
	 * Set parameter.
	 * 
	 * @param string $key Parameter name.
	 * @param mixed $value Parameter value.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function setParameter($key, $value) {
		$this->querySegments['parameters'][$key] = $value;
		return $this;
	}

	/**
	 * Set multiple parameters.
	 * 
	 * @param Array $parameters Parameter list.
	 * @param bool $merge Optional. Merge parameters or override.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function setParameters($parameters, $merge = false) {
		$this->querySegments['parameters'] = $merge
			? array_merge($this->querySegments['parameters'], $parameters) 
			: $parameters;
		return $this;
	}

	/**
	 * Add parameters.
	 * 
	 * @param Array $parameters Parameter list.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function addParameters($parameters) {
		$this->setParameters($parameters, true);
		return $this;
	}

	/**
	 * Get parameter.
	 * 
	 * @param string $key Parameter name.
	 * 
	 * @return mixed Parameter value.
	 */
	public function getParameter($key) {
		if ($this->hasParameter($key))
			return $this->querySegments['parameters'][$key];
	}

	/**
	 * Get all aprameters.
	 * 
	 * @return Array Parameter list.
	 */
	public function getParameters() {
		return $this->querySegments['parameters'];
	}

	/**
	 * Check if parameter exists.
	 * 
	 * @param string $key Parameter name.
	 * 
	 * @return bool True if parameter exists, false otherwise.
	 */
	public function hasParameter($key) {
		return (array_key_exists($key, $this->querySegments['parameters']));
	}

	/**
	 * Setup a delete query.
	 * 
	 * @param string $delete Table name.
	 * @param string $alias Table alias.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function delete($delete, $alias = 'this') {
		$this->querySegments['type'] = self::DELETE;
		$this->querySegments['delete'] = Array($delete, $alias);
		return $this;
	}

	/**
	 * Setup a insert query.
	 * 
	 * @param string $insert Table name.
	 * @param string $columns Table columns.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function insert($insert, $columns = null) {
		$this->querySegments['type'] = self::INSERT;
		$this->querySegments['insert'] = Array($insert, $columns);
		return $this;
	}

	/**
	 * Setup a select query.
	 * 
	 * @param string $select Columns to select.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function select($select) {
		$this->querySegments['type'] = self::SELECT;
		$this->querySegments['select'] = is_array($select) ? $select : array($select);
		return $this;
	}

	/**
	 * Add columns to select.
	 * 
	 * @param string $select Columns to select.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function addSelect($select) {
		$this->querySegments['select'] = array_merge(
			$this->querySegments['select']
			,is_array($select) ? $select : array($select)
		);
		return $this;
	}

	/**
	 * Setup a update query.
	 * 
	 * @param string $update Table name.
	 * @param string $alias Table alias.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function update($update, $alias = 'this') {
		$this->querySegments['type'] = self::UPDATE;
		$this->querySegments['update'] = Array($update, $alias);
		return $this;
	}

	/**
	 * Make select query distinct.
	 * 
	 * @param bool $distinct Make query distinct.
	 */
	public function distinct($distinct = true) {
		$this->querySegments['distinct'] = $distinct;
	}

	/**
	 * Set what table you want to select from in a select query.
	 * 
	 * @param string $table Table name.
	 * @param string $alias Optional. Table Alias.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function from($table, $alias = 'this') {
		$this->querySegments['from'] = Array($table, $alias);
		return $this;
	}

	/**
	 * Add table join.
	 * 
	 * @param string $table Table to join.
	 * @param string $what What to join on.
	 * @param string $on What table to join on.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function join($table, $what, $on) {
		$table = is_array($table) ? $table : Array($table);
		$this->querySegments['joins'][] = Array(
			'type' => 'join'
			,'table' => $table
			,'what' => $what
			,'on' => $on
		);
		return $this;
	}

	/**
	 * Add table inner join.
	 * 
	 * @param string $table Table to join.
	 * @param string $what What to join on.
	 * @param string $on What table to join on.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function innerJoin($table, $what, $on) {
		$table = is_array($table) ? $table : Array($table);
		$this->querySegments['joins'][] = Array(
			'type' => 'inner'
			,'table' => $table
			,'what' => $what
			,'on' => $on
		);
		return $this;
	}

	/**
	 * Add table full join.
	 * 
	 * @param string $table Table to join.
	 * @param string $what What to join on.
	 * @param string $on What table to join on.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function fullJoin($table, $what, $on) {
		$table = is_array($table) ? $table : Array($table);
		$this->querySegments['joins'][] = Array(
			'type' => 'full'
			,'table' => $table
			,'what' => $what
			,'on' => $on
		);
		return $this;
	}

	/**
	 * Add table left join.
	 * 
	 * @param string $table Table to join.
	 * @param string $what What to join on.
	 * @param string $on What table to join on.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function leftJoin($table, $what, $on) {
		$table = is_array($table) ? $table : Array($table);
		$this->querySegments['joins'][] = Array(
			'type' => 'left'
			,'table' => $table
			,'what' => $what
			,'on' => $on
		);
		return $this;
	}

	/**
	 * Add table right join.
	 * 
	 * @param string $table Table to join.
	 * @param string $what What to join on.
	 * @param string $on What table to join on.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function rightJoin($table, $what, $on) {
		$table = is_array($table) ? $table : Array($table);
		$this->querySegments['joins'][] = Array(
			'type' => 'right'
			,'table' => $table
			,'what' => $what
			,'on' => $on
		);
		return $this;
	}

	/**
	 * Add where constraint.
	 * 
	 * @param string $where Where constraint.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function where($where) {
		$this->querySegments['where'] = $where;
		return $this;
	}

	/**
	 * Add 'or' where constraint.
	 * 
	 * @param string $where Where constraint.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function orWHERE($where) {
		$this->querySegments['extraWhere'][] = Array('type' => 'or', 'where' => $where);
		return $this;
	}

	/**
	 * Add additional where constraint.
	 * 
	 * @param string $where Where constraint.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function andWHERE($where) {
		$this->querySegments['extraWhere'][] = Array('type' => 'and', 'where' => $where);
		return $this;
	}

	/**
	 * Add order by clause.
	 * 
	 * @param mixed $order Order by string, field or two apart array [field, direction].
	 * @param string $dir Order by direction.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function orderBy($order, $dir = null) {
		if (!$dir) {
			$order = is_array($order) ? $order : explode(' ', $order);
			if (sizeof($order) < 2)
				$order[] = 'asc';
		}
		else
			$order = Array($order, $dir);
		$this->querySegments['order'][] = $order;
		return $this;
	}

	/**
	 * Add order by clause.
	 * 
	 * @param mixed $order Order by string, field or two apart array [field, direction].
	 * @param string $dir Order by direction.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function addOrderBy($order, $dir = null) {
		$this->orderBy($order, $dir);
	}

	/**
	 * Set first result, the start position in the data result.
	 * 
	 * @param int $offset First result, starting position.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function setFirstResult($offset) {
		$this->querySegments['offset'] = (int)$offset;
		return $this;
	}

	/**
	 * Set maximum amount of results to get.
	 * 
	 * @param int $limit Max result count.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function setMaxResults($limit) {
		$this->querySegments['limit'] = (int)$limit;
		return $this;
	}

	/**
	 * Set values for insert query.
	 * 
	 * @param Array Values to set.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function values($values) {
		if (is_array($values)) {
			$valueStr = '';
			$valueCounter = -1;
			foreach ($values as $key => $value) {
				$valueCounter++;
				$valueStr .= ":value_{$this->counter}_{$valueCounter},";
				$this->querySegments['parameters']["value_{$this->counter}_{$valueCounter}"] = $value;
			}
			if ($valueStr != '') {
				$valueStr = rtrim($valueStr, ',');
				$this->querySegments['values'][] = $valueStr;
			}
			$this->counter++;
		}
		else {
			$this->querySegments['values'][] = $values;
		}
		return $this;
	}

	/**
	 * Set values for update query.
	 * 
	 * @param mixed $key Field name or array of field name value pairs.
	 * @param mixed $value Optional. Value to set.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function set($key, $value = null) {
		//--need to be values string ans add to parameters
		if (is_array($key)) {
			$this->querySegments['set'] = array_merge($this->querySegments['set'], $key);
			$index = $this->counter++;
			foreach ($key as $k => $v) {
				if (strpos($v, ':') !== 0) {
					$valueKey = "set_{$index}_{$k}";
					$this->querySegments['parameters'][$valueKey] = $v;
					$v = ":{$valueKey}";
				}
				$this->querySegments['set'][$k] = $v;
			}
		}
		else {
			if (strpos($value, ':') !== 0) {
				$index = $this->counter++;
				$valueKey = "set_{$index}_{$key}";
				$this->querySegments['parameters'][$valueKey] = $value;
				$value = ":{$valueKey}";
			}
			$this->querySegments['set'][$key] = $value;
		}
		return $this;
	}

	/**
	 * Set group by clause.
	 * 
	 * @param string $group Group by clause.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function groupBy($group) {
		$this->querySegments['groupBy'][] = $group;
		return $this;
	}

	/**
	 * Add group by clause.
	 * 
	 * @param string $group Group by clause.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function addGroupBy($group) {
		$this->querySegments['groupBy'][] = $group;
		return $this;
	}

	/**
	 * Set having caluse.
	 * 
	 * @param string $having Having statement.
	 * @param mixed $equalTo Optional. Value equal to.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function having($having, $equalTo = null) {
		$hasEqualTo = sizeof(func_get_args()) == 2;
		if ($hasEqualTo) {
			$count = $this->counter++;
			$this->querySegments['having'] = "{$having}=:having_{$count}";
			$this->querySegments['parameters']["having_{$count}"] = $equalTo;
		}
		else
			$this->querySegments['having'] = $having;
		return $this;
	}

	/**
	 * Add additional having caluse.
	 * change: to be removed, here for backwards compatibility.
	 * 
	 * @param string $having Having statement.
	 * @param mixed $equalTo Optional. Value equal to.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function addHaving($having, $equalTo = null) {
		$this->andHaving($having, $equalTo);
		return $this;
	}

	/**
	 * Add additional having caluse.
	 * change: to be removed, here for backwards compatibility.
	 * 
	 * @param string $having Having statement.
	 * @param mixed $equalTo Optional. Value equal to.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function andHaving($having, $equalTo = null) {
		$hasEqualTo = sizeof(func_get_args()) == 2;
		if ($hasEqualTo) {
			$count = $this->counter++;
			$this->querySegments['extraHaving'][] = Array(
				'type' => 'and'
				,'having' => "{$having}=:having_{$count}"
			);
			$this->querySegments['parameters']["having_{$count}"] = $equalTo;
		}
		else {
			$this->querySegments['extraHaving'][] = Array(
				'type' => 'and'
				,'having' => $having
			);;
		}
		return $this;
	}

	/**
	 * Add or having caluse.
	 * 
	 * @param string $having Having statement.
	 * @param mixed $equalTo Optional. Value equal to.
	 * 
	 * @return QueryBuilder Current instance.
	 */
	public function orHaving($having, $equalTo = null) {
		$hasEqualTo = sizeof(func_get_args()) == 2;
		if ($hasEqualTo) {
			$count = $this->counter++;
			$this->querySegments['extraHaving'][] = Array(
				'type' => 'or'
				,'having' => "{$having}=:having_{$count}"
			);
			$this->querySegments['parameters']["having_{$count}"] = $equalTo;
		}
		else {
			$this->querySegments['extraHaving'][] = Array(
				'type' => 'or'
				,'having' => $having
			);;
		}
		return $this;
	}

	/**
	 * Get generate SQL statement.
	 * 
	 * @return string SQL statement.
	 */
	public function getSQL() {
		return $this->buildQuery();
	}

	/**
	 * Get SQL parts.
	 * 
	 * @param Array $parts Desired SQL parts.
	 * 
	 * @return Array SQL parts.
	 */
	public function getSQLParts(Array $parts) {
		$sqlParts = Array();
		foreach ($parts as $part) {
			$sqlParts[$part] = $this->getSQLPart($part);
		}
	}

	/**
	 * Get SQL part.
	 * 
	 * @param string $part SQL part name.
	 * 
	 * @return mixed SQL part.
	 */
	public function getSQLPart($part) {
		if (array_key_exists($part, $this->querySegments)) {
			return $this->querySegments[$part];
		}
	}

	/**
	 * Reset SQL parts.
	 * 
	 * @param Array $parts Optional. Parts to be reset.
	 */
	public function resetSQLParts($parts = null) {
		if (is_array($parts)) {
			foreach ($parts as $part)
				$this->resetSQLPart($part);
		}
		else
			$this->querySegments = $this->defaultSegments();
	}

	/**
	 * Reset SQL part.
	 * 
	 * @param string $part SQL part to be reset.
	 */
	public function resetSQLPart($part) {
		if (isset($this->querySegments[$part])) {
			if (in_array($this->querySegments[$part], [
				'select', 'insert', 'delete', 'update'
				,'from', 'joins', 'extraWhere', 'order'
				,'values', 'set', 'groupBy', 'extraHaving'
				,'parameters'
			])) {
				$this->querySegments[$part] = Array();
			}
			else {
				$this->querySegments[$part] = null;
			}
		}
	}
}