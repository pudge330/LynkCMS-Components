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
 * Database service intended to simplify and provide an abstract way to interact with a database.
 */
class DatabaseService {

	/**
	 * @var mixed Connection or ConnectionWrapped instance.
	 */
	protected $connection;

	/**
	 * @param mixed $connection PDO or ConnectionWrapped instance.
	 */
	public function __construct($connection) {
		if (!($connection instanceof PDO || $connection instanceof ConnectionWrap)) {
			throw new Exception('QueryBuilder: First argument of constructor must be instance of PDO or ConnectionWrap.');
		}
		$this->connection = $connection instanceof Connection || $connection instanceof ConnectionWrap
			? $connection
			: new ConnectionWrap($connection);
	}

	/**
	 * Get PDO instance.
	 * 
	 * @return PDO PDO connection.
	 */
	public function getPDO() {
		return $this->connection->pdo();
	}

	/**
	 * Get connection instance.
	 * 
	 * @return Connection|ConnectionWrapped Connection instance.
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * Create a new instance of QueryBuilder.
	 * 
	 * @return QueryBuilder New QueryBuilder instance.
	 */
	protected function newQueryBuilder() {
		return new QueryBuilder($this->connection);
	}

	/**
	 * Generic find method, can handle most use-cases.
	 * 
	 * @param string $table Table name.
	 * @param mixed $criteria Optional. Various criteria and parameters to narrow scope of data to find. Or an id value.
	 *                                  - select:     what to select, defaults to alias or 'this'
	 *                                  - alias:      table alias key, default is 'this'
	 *                                  - id:         id or array of ids
	 *                                  - idColumn:   id column name
	 *                                  - joins:      table joins
	 *                                  - where:      where string/constraints
	 *                                  - orWhere:    or where constraints
	 *                                  - criteria:   query criteria, column[expression] value pairs
	 *                                  - parameters: where/criteria parameters
	 * @param Array $mod Optional. Query modifiers.
	 *                             - order:    query select order
	 *                             - limit:    limit value
	 *                             - offset:   offset result value
	 *                             - distinct: only select distinct values
	 *                             - scalar:   return scalar results not entities
	 * 
	 * @return mixed Nested-Array or singular array value of data. Null if singlular result expected and no record found.
	 */
	public function find($table, $criteria = Array(), $mod = Array()) {
		if (is_numeric($criteria) || is_string($criteria)) {
			$idColumn = isset($mod['idColumn']) ? $mod['idColumn'] : 'uid';
			if (preg_match('/^(.+):(\d+)$/', $criteria, $match)) {
				$idColumn = $match[1];
				$criteria = $match[2];
			}
			$criteria = Array(
				'criteria' => Array(
					$idColumn => $criteria
				)
				,'idColumn' => $idColumn
			);
			$mod = array_merge($mod, Array('limit' => 1));
			return $this->find($table, $criteria, $mod);
		}
		$criteria['delete'] = false;
		$criteria['update'] = false;
		$criteria['insert'] = false;
		$alias = isset($criteria['alias']) ? $criteria['alias'] : 'this';
		$criteria['alias'] = $alias;
		$select = isset($criteria['select']) ? $criteria['select'] : '*';
		$criteria['select'] = $select;
		$mod = array_merge(Array(
			'order' => null
			,'limit' => null
			,'offset' => null
			,'distinct' => false
			,'scalar' => false
			,'EntityClass' => '\\LynkCMS\\Component\\Database\\AbstractEntity'
		), $mod);
		$isScalar = isset($mod['scalar']) && $mod['scalar'] ? true : false;
		$isSqlFnSelect = is_string($select)
			? preg_match('/\s*(avg|count|max|min|sum)\s*\(\w+\)/', strtolower($select))
			: false;
		$result = $this->createQueryBuilder($table, $criteria, $mod)->query();
		if ($result['result'] && !$isScalar) {
			$result['data'] = $mod['EntityClass']::hydrate($result['data']);
		}
		if (!$result['result'] || !$result['rowCount']) {
			if ($mod['limit'] == 1)
				return null;
			else
				return Array();
		}
		else {
			if ($isSqlFnSelect) {
				return array_values($result['data'][0])[0];
			}
			else if ($mod['limit'] == 1)
				return $result['data'][0];
			else
				return $result['data'];
		}
	}

	/**
	 * Generic find method, can handle most use-cases. Returns only one result.
	 * 
	 * @param string $table Table name.
	 * @param mixed $criteria Optional. Various criteria and parameters to narrow scope of data to find. Or an id value.
	 *                                  See find().
	 * @param Array $mod Optional. Query modifiers.
	 *                                  See find().
	 * 
	 * @return mixed Singular array value or null no record found.
	 */
	public function findOne($table, $criteria = Array(), $mod = Array()) {
		$mod['limit'] = 1;
		return $this->find($table, $criteria, $mod);
	}

	/**
	 * Generic find method, can handle most use-cases. Returns scalar results instead of entities.
	 * 
	 * @param string $table Table name.
	 * @param mixed $criteria Optional. Various criteria and parameters to narrow scope of data to find. Or an id value.
	 *                                  See find().
	 * @param Array $mod Optional. Query modifiers.
	 *                                  See find().
	 * 
	 * @return mixed Nested-Array ofs calar data records.
	 */
	public function findScalar($table, $criteria = Array(), $mod = Array()) {
		$mod['scalar'] = true;
		return $this->find($table, $criteria, $mod);
	}

	/**
	 * Generic find method, can handle most use-cases. Returns only one scalar result.
	 * 
	 * @param string $table Table name.
	 * @param mixed $criteria Optional. Various criteria and parameters to narrow scope of data to find. Or an id value.
	 *                                  See find().
	 * @param Array $mod Optional. Query modifiers.
	 *                                  See find().
	 * 
	 * @return mixed Single array or null no record found.
	 */
	public function findOneScalar($table, $criteria = Array(), $mod = Array()) {
		$mod['limit'] = 1;
		$mod['scalar'] = true;
		return $this->find($table, $criteria, $mod);
	}

	/**
	 * Generic delete method.
	 * 
	 * @param string $table Table name.
	 * @param mixed $criteria Optional. Various criteria and parameters to narrow scope of data to find. Or an id value.
	 *                                  See find().
	 * 
	 * @return bool? True if successful, false otherwise. Maybe the number of records deleted.
	 */
	public function delete($table, $criteria = Array()) {
		$criteria['delete'] = true;
		$criteria['update'] = false;
		$criteria['insert'] = false;
		return $this->createQueryBuilder($table, $criteria)->query()['result'];
	}

	/**
	 * Generic update method.
	 * 
	 * @param string $table Table name.
	 * @param mixed $criteria Optional. Various criteria and parameters to narrow scope of data to find. Or an id value.
	 *                                  See find().
	 * 
	 * @return bool? True if successful, false otherwise. Maybe the number of records altered.
	 */
	public function update($table, $criteria = Array()) {
		$criteria['update'] = true;
		$criteria['delete'] = false;
		$criteria['insert'] = false;
		return $this->createQueryBuilder($table, $criteria)->query()['result'];
	}

	/**
	 * Insert data into table.
	 * 
	 * @param string $table Table name.
	 * @param mixed $criteria Optional. Various criteria and parameters to narrow scope of data to find. Or an id value.
	 *                                  See find().
	 * 
	 * @return int Insert id.
	 */
	public function insert($table, $criteria = Array()) {
		$criteria['insert'] = true;
		$criteria['delete'] = false;
		$criteria['update'] = false;
		$result = $this->createQueryBuilder($table, $criteria)->query();
		return $result['result'] ? $result['insertId'] : null;
	}

	/**
	 * Create query builder instance.
	 * 
	 * @param string $table Table name.
	 * @param mixed $criteria Optional. Various criteria and parameters to narrow scope of data to find. Or an id value.
	 *                                  See find().
	 * @param Array $mod Optional. Query modifiers.
	 *                                  See find().
	 * 
	 * @return QueryBuilder New Querybuilder instance.
	 */
	public function createQueryBuilder($table, $criteria = Array(), $mod = Array()) {
		/*

			Want to change the way the 'criteria' is handled in the query builders (DatabaseService and DoctrineService).
			The 'criteria', the ability to add fields as keys with optional special operators will not be handles in combination
			with the 'where'. Will throw deprecation error when a key outside the predeterrmined ones is present but still allow
			it to work as normal.

			$criteria
				- alias
				- select
				- delete
				- update
				- set
				- id
				- idColumn
				- joins
				- criteria
				- where
				- orWhere
				- parameters

			$mod
				- order
				- limit
				- offset
				- group
				- distinct
				- scalar

		*/
		$alias = isset($criteria['alias']) ? $criteria['alias'] : 'this';
		$select = isset($criteria['select']) ? $criteria['select'] : '*';
		$delete = isset($criteria['delete']) ? $criteria['delete'] : null;
		$update = isset($criteria['update']) ? $criteria['update'] : null;
		$insert = isset($criteria['insert']) ? $criteria['insert'] : null;

		$isInsert = ($insert || $delete);
		
		$set = isset($criteria['set']) ? $criteria['set'] : null;
		$id = isset($criteria['id']) ? $criteria['id'] : Array();
		$idColumn = isset($criteria['idColumn']) ? $criteria['idColumn'] : 'uid';
		$joins = isset($criteria['joins']) ? $criteria['joins'] : Array();
		$whereCriteria = isset($criteria['criteria']) ? $criteria['criteria'] : Array();
		$where = isset($criteria['where']) ? $criteria['where'] : Array();
		$orWhere = isset($criteria['orWhere']) ? $criteria['orWhere'] : Array();
		$parameters = isset($criteria['parameters']) ? $criteria['parameters'] : Array();
		$mod = array_merge(Array(
			'order' => null
			,'limit' => null
			,'offset' => null
			,'group' => null
			,'distinct' => false
			,'scalar' => false
		), $mod);

		$columns = isset($criteria['columns']) ? $criteria['columns'] : null;
		$values = isset($criteria['values']) ? $criteria['values'] : Array();
		
		unset($criteria['alias'], $criteria['select'], $criteria['delete'], $criteria['update'], $criteria['insert'], $criteria['set'], $criteria['columns'], $criteria['values'], $criteria['id'], $criteria['idColumn'], $criteria['joins'], $criteria['criteria'], $criteria['where'], $criteria['orWhere'], $criteria['parameters']);
		$mod['order'] = $mod['order'] ? $this->resolveOrderBy($mod['order'], $select, $alias) : null;
		list($whereString, $whereParameters) = $this->buildWhereQueryParts(array_merge($criteria, $whereCriteria), Array($idColumn, $id), $where, $alias, $isInsert);
		$parameters = array_merge($whereParameters, $parameters);
		$qb = $this->newQueryBuilder();

		if ($delete) {
			$qb->delete($table, $alias);
		}
		else if ($insert) {
			$qb->insert($table, $columns)
			   ->values($values);
		}
		else if ($update) {
			$qb->update($table, $alias);
			$count = -1;
			$setParameters = Array();
			foreach ($set as $key => $value) {
				$count++;
				$paramName = "set_param_{$count}";
				if (strpos($key, '.') === false)
					$key = "{$key}";
				$qb->set($key, ":{$paramName}");
				$setParameters[$paramName] = $value;
			}
			$parameters = array_merge($setParameters, $parameters);
		}
		else {
			$qb->select($select);
			if ($mod['distinct'])
				$qb->distinct($mod['distinct']);
			if ($alias)
				$qb->from($table, $alias);
		}

		foreach ($joins as $key => $join) {
			$jalias = $key;
			$table = $what = $on = $type = null;
			if (is_array($join) && sizeof($join) >= 3) {
				if (isset($join['table'])) {
					$join = array_merge(Array(
						'table' => ''
						,'what' => ''
						,'on' => ''
						,'type' => 'inner'
					), $join);
				}
				else {
					$join = array_merge(Array(
						'table' => ''
						,'what' => ''
						,'on' => ''
						,'type' => 'inner'
					), Array(
						'table' => $join[0]
						,'what' => $join[1]
						,'on' => $join[2]
						,'type' => isset($join[3]) ? $join[3] : 'inner'
					));
				}
				if (!is_array($join['table'])) {
					if (strpos($join['table'], ' ') !== false) {
						$split = explode(' ', $join['table']);
						if (!isset($join['alias']))
							$join['alias'] = $split[1];
						$join['table'] = $split[0];
					}
					else if (strpos($join['on'], '.') !== false) {
						$split = explode(' ', $join['on']);
						if (!$join['alias'])
							$join['alias'] = $split[1];
					}
					else if (!is_numeric($jalias)) {
						$join['alias'] = $jalias;
					}
					$table = Array($join['table']);
				}
				else
					$table = $join['table'];
				if (isset($join['alias']) && sizeof($table) < 2)
					$table[] = $join['alias'];
				$type = $join['type'];
				$what = $join['what'];
				$on = $join['on'];
			}
			else {
				if (preg_match(
					'/^(?:(left|right|full|inner)(?::| )?)?(\w+)(?: (\w+))? (?:(\w+)\.)?(\w+)=(?:(\w+)\.)?(\w+)$/'
					,$join
					,$match
				)) {
					$type = $match[1] ?: 'inner';
					$table = Array($match[2]);
					if ($match[3])
						$table[] = $match[3];
					else if ($match[6])
						$table[] = $match[6];
					else if (!is_numeric($alias))
						$table[] = $alias;
					$what = $match[4] ? "{$match[4]}.{$match[5]}" : $match[5];
					$on = $match[6] ? "{$match[6]}.{$match[7]}" : $match[7];
				}
			}
			if ($type) {
				switch ($type) {
					case 'right':
					case 'rightJoin':
						$qb->rightJoin($table, $what, $on);
					break;
					case 'left':
					case 'leftJoin':
						$qb->leftJoin($table, $what, $on);
					break;
					case 'full':
					case 'fullJoin':
						$qb->fullJoin($table, $what, $on);
					break;
					case 'inner':
					case 'innerJoin':
					default:
						$qb->innerJoin($table, $what, $on);
					break;
				}
			}
		}
		$hasWhere = false;
		if ($whereString && $whereString != '') {
			$qb->where($whereString);
			$hasWhere = true;
		}
		foreach ($orWhere as $or) {
			if ($hasWhere) {
				$qb->orWhere($or);
			}
			else {
				$qb->where($or);
				$hasWhere = true;
			}
		}
		$firstOrder = true;
		if ($mod['order']) {
			foreach ($mod['order'] as $order) {
				if ($firstOrder) {
					$qb->orderBy($order[0], $order[1]);
					$firstOrder = false;
				}
				else
					$qb->addOrderBy($order[0], $order[1]);
			}
		}
		if ($mod['limit']) {
			$qb->setMaxResults($mod['limit']);
		}
		if ($mod['offset']) {
			$qb->setFirstResult($mod['offset']);
		}
		if ($mod['group'] && sizeof($mod['group']) > 0) {
			$groups = $mod['group'];
			$first = array_shift($groups);
			$qb->groupBy("{$first}");
			foreach ($groups as $g) {
				$qb->addGroupBy("{$g}");
			}
		}
		if (sizeof($parameters) > 0)
			$qb->setParameters($parameters);
		return $qb;
	}

	/**
	 * Resolve order by options.
	 * 
	 * @param mixed $orderBy Order by value or array of order by values.
	 * @param string $select Select option.
	 * @param string $alias Table alias.
	 * 
	 * @return Array Order by options.
	 */
	public function resolveOrderBy($orderBy, $select, $alias) {
		if (is_string($orderBy)) {
			$tmp = array(explode(' ', $orderBy));
			$orderBy = $tmp;
		}
		$final = Array();
		foreach ($orderBy as $order) {
			if (!is_array($order))
				$order = explode(' ', $order);
			if (sizeof($order) < 2) {
				if (strpos($order[0], ' ') !== false) {
					$order = explode(' ', $order[0]);
				}
				else {
					$order[] = 'asc';
				}
			}
			if (in_array($order[1], Array('a', 'asc', '<'))) {
				$order[1] = 'asc';
			}
			else if (in_array($order[1], Array('d', 'desc', '>'))) {
				$order[1] = 'desc';
			}
			else {
				$order[1] = 'asc';
			}
			$select = is_array($select) ? implode(',', $select) : $select;
			if (strpos($order[0], '.') === false && strpos($select, " by {$order[0]}") === false) {
				$order[0] = "{$alias}.{$order[0]}";
			}
			$final[] = $order;
		}
		return $final;
	}

	/**
	 * Build where string for query.
	 * 
	 * @param Array $criteria Criteria key[expression] and value pairs.
	 * @param mixed $id Id(,s) or array of ids.
	 * @param Array $where Array of where conditions.
	 * @param string $alias Optional. Table alias.
	 * @param bool $isInsert Optional. Is query insert query.
	 * 
	 * @return Array Final array string and array of parameters.
	 */
	public function buildWhereQueryParts($criteria, $id, $where, $alias = 'this', $isInsert = false) {
		$criteriaString = $whereString = $idString = '';
		$parameters = Array();
		$count = -1;
		$idCol = $id[0];
		if (strpos($idCol, '.') === false && !$isInsert)
			$idCol = "{$alias}.{$idCol}";
		$ids = $id[1];
		if (is_numeric($ids))
			$ids = Array($ids);
		else if (!is_array($ids))
			$ids = explode(',', $ids);
		foreach ($ids as $id) {
			$count++;
			$idString .= "{$idCol} = :id_param_{$count} OR ";
			$parameters["id_param_{$count}"] = $id;
		}
		$idString = rtrim($idString, ' OR ');
		$count = -1;
		foreach ($criteria as $col => $value) {
			$count++;
			$col = explode(' ', $col);
			$column = array_shift($col);
			$col = implode(' ', $col);
			$comparison = strtoupper($col != '' ? $col : '=');
			$tmpAlias = (
				strpos($column, '.') === false &&
				!$isInsert
			) ? "{$alias}." : '';
			if (in_array($comparison, Array(
				'=', '!=', 'LIKE', 'NOT LIKE', '<', '<=', '>', '>=', '<>'
			))) {
				if ($value === null) {
					if ($comparison == '!=')
						$criteriaString .= "{$tmpAlias}{$column} IS NOT NULL AND ";
					else
						$criteriaString .= "{$tmpAlias}{$column} IS NULL AND ";
				}
				else if (is_array($value)) {
					$whereInClause = ":criteria__param_{$count}";
					$whereInClause = in_array($comparison, Array('!=', '<>', 'NOT IN', 'NOT'))
						? "{$tmpAlias}{$column} NOT IN($whereInClause) AND "
						: "{$tmpAlias}{$column} IN($whereInClause) AND "
					;
					$criteriaString .= $whereInClause;
					$parameters["criteria__param_{$count}"] = $value;
					// $whereInClause = '';
					// $inCount = -1;
					// foreach ($value as $v) {
					// 	$inCount++;
					// 	$whereInClause .= ":criteria__param_{$count}_{$inCount},";
					// 	$parameters["criteria__param_{$count}_{$inCount}"] = $v;
					// }
					// $whereInClause = rtrim($whereInClause, ',');
					// $whereInClause = in_array($comparison, Array('!=', '<>', 'NOT IN', 'NOT'))
					// 	? "{$tmpAlias}{$column} NOT IN($whereInClause) AND "
					// 	: "{$tmpAlias}{$column} IN($whereInClause) AND "
					// ;
					// $criteriaString .= $whereInClause;
				}
				else {
					$criteriaString .= "{$tmpAlias}{$column} {$comparison} :criteria__param_{$count} AND ";
					$parameters["criteria__param_{$count}"] = $value;
				}
			}
			else if (in_array($comparison, Array('IS NULL', 'IS NOT NULL'))) {
				$criteriaString .= "{$tmpAlias}{$column} {$comparison} AND ";
			}
			else {
				$criteriaString .= "{$tmpAlias}{$column} = :criteria__param_{$count} AND ";
				$parameters["criteria__param_{$count}"] = $value;
			}
		}
		$criteriaString = rtrim($criteriaString, ' AND ');
		$whereSize = sizeof($where);
		if ($whereSize > 1) {
			foreach ($where as $wstring) {
				$whereString .= "({$wstring}) AND ";
			}
			$whereString = rtrim($whereString, ' AND ');
			$whereString = "({$whereString})";
		}
		else if ($whereSize) {
			$tmpWhere = !preg_match('/^\((.*)\)$/', $where[0]) ? "({$where[0]})" : $where[0];
			$whereString = $tmpWhere;
		}
		$returnString = '';
		if ($criteriaString != '' && $whereString != '')
			$returnString = "({$criteriaString} AND {$whereString})";
		else if ($criteriaString != '')
			$returnString = "({$criteriaString})";
		else if ($whereString != '')
			$returnString = "{$whereString}";
		if ($idString != '' && $returnString != '') {
			$returnString = "({$idString}) OR {$returnString}";
		}
		else if ($idString != '') {
			$returnString = "{$idString}";
		}
		return Array($returnString, $parameters);
	}
}