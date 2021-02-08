<?php
namespace LynkCMS\Component\Database;

use Exception;
use PDO;
use LynkCMS\Component\Connection\Connection;
use LynkCMS\Component\Connection\ConnectionWrap;

class QueryBuilder {
	const DELETE = 'delete';
	const INSERT = 'insert';
	const SELECT = 'select';
	const UPDATE = 'update';
	const UPSERT = 'upsert';
	const STATE_DIRTY = 'dirty';
	const STATE_CLEAN = 'clean';
	protected $connection;
	protected $querySegments;
	protected $counter;
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
	public function getState() {
		$defaults = $this->defaultSegments();
		if ($this->querySegments === $defaults)
			return self::STATE_CLEAN;
		else
			return self::STATE_DIRTY;
	}
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
	protected function resetVariables() {
		$this->querySegments = $this->defaultSegments();
	}
	public function getConnection() {
		return $this->connection;
	}
	public function getRootAlias() {
		if ($this->querySegments['type'] == self::SELECT)
			return isset($this->querySegments['from'][1]) ? $this->querySegments['from'][1] : null;
		else if ($this->querySegments['type'] == self::DELETE)
			return isset($this->querySegments['delete'][1]) ? $this->querySegments['delete'][1] : null;
		else if ($this->querySegments['type'] == self::UPDATE)
			return isset($this->querySegments['update'][1]) ? $this->querySegments['update'][1] : null;
	}
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
	public function getRootTable() {
		if ($this->querySegments['type'] == self::SELECT)
			return isset($this->querySegments['from'][0]) ? $this->querySegments['from'][0] : null;
		else if ($this->querySegments['type'] == self::DELETE)
			return isset($this->querySegments['delete'][0]) ? $this->querySegments['delete'][0] : null;
		else if ($this->querySegments['type'] == self::UPDATE)
			return isset($this->querySegments['update'][0]) ? $this->querySegments['update'][0] : null;
	}
	public function query($data = null, $delayFetch = false) {
		$query = $this->buildQuery();
		if (sizeof($this->querySegments['parameters'])) {
			$data = is_array($data) ? array_merge($this->querySegments['parameters'], $data) : $this->querySegments['parameters'];
		}
		$res = $this->connection->run($query, $data, $delayFetch);
		return $res;
	}
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
	public function setParameter($key, $value) {
		$this->querySegments['parameters'][$key] = $value;
		return $this;
	}
	public function setParameters($parameters, $merge = false) {
		$this->querySegments['parameters'] = $merge
			? array_merge($this->querySegments['parameters'], $parameters) 
			: $parameters;
		return $this;
	}
	public function addParameters($parameters) {
		$this->setParameters($parameters, true);
		return $this;
	}
	public function getParameter($key) {
		if ($this->hasParameter($key))
			return $this->querySegments['parameters'][$key];
	}
	public function getParameters() {
		return $this->querySegments['parameters'];
	}
	public function hasParameter($key) {
		return (array_key_exists($key, $this->querySegments['parameters']));
	}
	public function delete($delete, $alias = 'this') {
		$this->querySegments['type'] = self::DELETE;
		$this->querySegments['delete'] = Array($delete, $alias);
		return $this;
	}
	public function insert($insert, $columns = null) {
		$this->querySegments['type'] = self::INSERT;
		$this->querySegments['insert'] = Array($insert, $columns);
		return $this;
	}
	public function select($select) {
		$this->querySegments['type'] = self::SELECT;
		$this->querySegments['select'] = is_array($select) ? $select : array($select);
		return $this;
	}
	public function addSelect($select) {
		$this->querySegments['select'] = array_merge(
			$this->querySegments['select']
			,is_array($select) ? $select : array($select)
		);
		return $this;
	}
	public function update($update, $alias = 'this') {
		$this->querySegments['type'] = self::UPDATE;
		$this->querySegments['update'] = Array($update, $alias);
		return $this;
	}
	// public function upsert($upsert, $columns = null) {
	// 	$this->querySegments['type'] = self::UPSERT;
	// 	$this->querySegments['upsert'] = Array($upsert, $columns);
	// 	return $this;
	// }
	public function distinct($distinct = true) {
		$this->querySegments['distinct'] = $distinct;
	}
	public function from($table, $alias = 'this') {
		$this->querySegments['from'] = Array($table, $alias);
		return $this;
	}
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
	public function where($where) {
		$this->querySegments['where'] = $where;
		return $this;
	}
	public function orWHERE($where) {
		$this->querySegments['extraWhere'][] = Array('type' => 'or', 'where' => $where);
		return $this;
	}
	public function andWHERE($where) {
		$this->querySegments['extraWhere'][] = Array('type' => 'and', 'where' => $where);
		return $this;
	}
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
	public function addOrderBy($order, $dir = null) {
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
	public function setFirstResult($offset) {
		$this->querySegments['offset'] = (int)$offset;
		return $this;
	}
	public function setMaxResults($limit) {
		$this->querySegments['limit'] = (int)$limit;
		return $this;
	}
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
	public function groupBy($group) {
		$this->querySegments['groupBy'][] = $group;
		return $this;
	}
	public function addGroupBy($group) {
		$this->querySegments['groupBy'][] = $group;
		return $this;
	}
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
	public function addHaving($having, $equalTo = null) {
		//--bc
		$this->andHaving($having, $equalTo);
		return $this;
	}
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
	public function getSQL() {
		return $this->buildQuery();
	}
	public function getSQLParts(Array $parts) {
		$sqlParts = Array();
		if (is_array($parts)) {
			foreach ($parts as $part) {
				$sqlParts[$part] = $this->getSQLPart($part);
			}
		}
	}
	public function getSQLPart($part) {
		if (array_key_exists($part, $this->querySegments)) {
			return $this->querySegments[$part];
		}
	}
	public function resetSQLParts($parts = null) {
		if (is_array($parts)) {
			foreach ($parts as $part)
				$this->resetSQLPart($part);
		}
		else
			$this->querySegments = $this->defaultSegments();
	}
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