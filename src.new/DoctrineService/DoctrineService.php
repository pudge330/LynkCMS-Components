<?php
namespace LynkCMS\Component\DoctrineService;

use Doctrine\ORM\EntityManager;

class DoctrineService {
	protected $entityManager;
	public function __construct(EntityManager $em) {
		$this->entityManager = $em;
	}
	public function getEntityManager() {
		if (!$this->entityManager->isOpen()) {
			$this->entityManager = $this->entityManager->create(
				$this->entityManager->getConnection(), $this->entityManager->getConfiguration()
			);
		}
		return $this->entityManager;
	}
	public function getReference($entity, $id) {
		return $this->getEntityManager()->getReference($entity, $id);
	}
	public function getPDO() {
		return $this->getEntityManager()->getConnection()->getWrappedConnection();
	}
	public function find($entity, $criteria = Array(), $mod = Array()) {
		if (is_numeric($criteria) || is_string($criteria)) {
			return $this->entityManager->getRepository($entity)->find($criteria);
		}
		$criteria['delete'] = false;
		$criteria['update'] = false;
		$alias = isset($criteria['alias']) ? $criteria['alias'] : 'this';
		$criteria['alias'] = $alias;
		$select = isset($criteria['select']) ? $criteria['select'] : $alias;
		$criteria['select'] = $select;
		$mod = array_merge(Array(
			'order' => null
			,'limit' => null
			,'offset' => null
			,'distinct' => false
			,'scalar' => false
		), $mod);
		$isSqlFnSelect = is_string($select)
			? preg_match('/\s*(avg|count|max|min|sum)\s*\(\w+\)/i', $select)
			: false;
		$resultMethod = 'getResult';
		if ($mod['scalar']) {
			$resultMethod = 'getArrayResult';
		}
		$result = $this->createQueryBuilder($entity, $criteria, $mod)->getQuery()->{$resultMethod}();
		if (!$result) {
			if ($mod['limit'] == 1)
				return null;
			else
				return Array();
		}
		else {
			if ($isSqlFnSelect) {
				return array_values($result[0])[0];
			}
			else if ($mod['limit'] == 1)
				return $result[0];
			else
				return $result;
		}
	}
	public function findOne($entity, $criteria = Array(), $mod = Array()) {
		$mod['limit'] = 1;
		return $this->find($entity, $criteria, $mod);
	}
	public function findScalar($entity, $criteria = Array(), $mod = Array()) {
		$mod['scalar'] = true;
		return $this->find($entity, $criteria, $mod);
	}
	public function findOneScalar($entity, $criteria = Array(), $mod = Array()) {
		$mod['limit'] = 1;
		$mod['scalar'] = true;
		return $this->find($entity, $criteria, $mod);
	}
	public function delete($entity, $criteria = Array()) {
		$criteria['delete'] = true;
		$criteria['update'] = false;
		return $this->createQueryBuilder($entity, $criteria)->getQuery()->getResult();
	}
	public function update($entity, $criteria = Array()) {
		$criteria['update'] = true;
		$criteria['delete'] = false;
		return $this->createQueryBuilder($entity, $criteria)->getQuery()->getResult();
	}
	public function insert($entity) {
		$this->persist($entity);
		return true;
	}
	public function create($entity, $data = Array()) {
		$class = $this->getEntityManager()->getRepository($entity)->getClassName();
		if ($class) {
			return new $class($data);
		}
		else {
			throw new Exception('Database::create() cannot find entity class {$class}');
		}
	}
	public function persist($entities) {
		try {
			$em = $this->getEntityManager();
			if (!is_array($entities))
				$entities = Array($entities);
			foreach ($entities as $entity)
				$em->persist($entity);
			if (sizeof($entities) > 0)
				$em->flush();
			return true;
		}
		catch (Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}
	public function remove($entities, $name = null) {
		$em = $this->getEntityManager();
		if (!is_array($entities)) {
			if(is_string($entities))
				$entities = explode(',', $entities);
			else
				$entities = Array($entities);
		}
		foreach ($entities as $entity) {
			if (!is_object($entity) && $name)
				$entity = $this->getReference($name, $entity);
			if (is_object($entity))
				$em->remove($entity);
		}
		$em->flush();
	}
	public function createQueryBuilder($entity, $criteria = Array(), $mod = Array()) {
		$alias = isset($criteria['alias']) ? $criteria['alias'] : 'this';
		$select = isset($criteria['select']) ? $criteria['select'] : $alias;
		$delete = isset($criteria['delete']) ? $criteria['delete'] : null;
		$update = isset($criteria['update']) ? $criteria['update'] : null;
		
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
			,'distinct' => false
			,'scalar' => false
		), $mod);
		unset($criteria['alias'], $criteria['select'], $criteria['delete'], $criteria['update'], $criteria['set'], $criteria['id'], $criteria['idColumn'], $criteria['joins'], $criteria['criteria'], $criteria['where'], $criteria['orWhere'], $criteria['parameters']);
		$mod['order'] = $mod['order'] ? $this->resolveOrderBy($mod['order'], $select, $alias) : null;
		list($whereString, $whereParameters) = $this->buildWhereQueryParts(array_merge($criteria, $whereCriteria), Array($idColumn, $id), $where, $alias);
		$parameters = array_merge($whereParameters, $parameters);
		$qb = $this->getEntityManager()->createQueryBuilder();
		if ($delete) {
			$qb->delete($entity, $alias);
		}
		else if ($update) {
			$qb->update($entity, $alias);
			$count = -1;
			$setParameters = Array();
			foreach ($set as $key => $value) {
				$count++;
				$paramName = "set_param_{$count}";
				if (strpos($key, '.') === false)
					$key = "{$alias}.{$key}";
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
				$qb->from($entity, $alias);
		}
		foreach ($joins as $key => $join) {
			$alias = $key;
			if (is_array($join)) {
				$alias = isset($join['alias']) ? $join['alias'] : $alias;
				$type = isset($join['type']) ? $join['type'] : 'inner';
				$column = isset($join['column']) ? $join['column'] : null;
			}
			else {
				$join = explode(' ', $join);
				$joinSize = sizeof($join);
				if ($joinSize == 3) {
					switch($join[0]) {
						case 'left':
						case 'left:':
							$type = 'left';
						break;
						case 'right':
						case 'right:':
							$type = 'right';
						break;
						default:
							$type = 'inner';
					}
					$column = $join[1];
					$alias = $join[2];
				}
				else if ($joinSize == 2) {
					$tmpJoin0 = strtolower($join[0]);
					if (strpos($tmpJoin0, 'left:') !== false) {
						$type = 'left';
						$column = $join[1];
					}
					else if (strpos($tmpJoin0, 'right:') !== false) {
						$type = 'right';
						$column = $join[1];
					}
					else if (preg_match('/^([a-zA-Z0-9-_]+:)$/', $tmpJoin0)) {
						$type = 'inner';
						$column = $join[1];
					}
					else {
						$type = 'inner';
						$column = $join[0];
						$alias = $join[1];
					}
				}
			}
			switch ($type) {
				case 'right':
					$qb->rightJoin($column, $alias);
				break;
				case 'left':
					$qb->leftJoin($column, $alias);
				break;
				default:
					$qb->join($column, $alias);
				break;
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
		if (sizeof($parameters) > 0)
			$qb->setParameters($parameters);
		return $qb;
	}
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
			if (in_array(strtolower($order[1]), Array('a', 'asc', '<'))) {
				$order[1] = 'asc';
			}
			else if (in_array(strtolower($order[1]), Array('d', 'desc', '>'))) {
				$order[1] = 'desc';
			}
			else {
				$order[1] = 'asc';
			}
			if (strpos($order[0], '.') === false && strpos($select, " by {$order[0]}") === false) {
				$order[0] = "{$alias}.{$order[0]}";
			}
			$final[] = $order;
		}
		return $final;
	}
	public function buildWhereQueryParts($criteria, $id, $where, $alias = 'this') {
		$criteriaString = $whereString = $idString = '';
		$parameters = Array();
		$count = -1;
		$idCol = $id[0];
		if (strpos($idCol, '.') === false)
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
			$comparison = strtoupper($col != '' ? trim($col) : '=');
			$tmpAlias = strpos($column, '.') !== false ? '' : "{$alias}.";
			// if ($comparison == '!=')
				// dump(['DoctrineService:001', $comparison, $value]);
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
					if (in_array($comparison, Array('!=', '<>', 'NOT IN', 'NOT'))) {
						$criteriaString .= "{$tmpAlias}{$column} NOT IN(:criteria__param_{$count}) AND ";
					}
					else {
						$criteriaString .= "{$tmpAlias}{$column} IN(:criteria__param_{$count}) AND ";
					}
					$parameters["criteria__param_{$count}"] = $value;
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
		foreach ($where as $wstring) {
			$whereString .= "({$wstring}) AND ";
		}
		$whereString = rtrim($whereString, ' AND ');
		$returnString = '';
		if ($criteriaString != '' && $whereString != '')
			$returnString = "({$criteriaString} AND {$whereString})";
		else if ($criteriaString != '')
			$returnString = "({$criteriaString})";
		else if ($whereString != '')
			$returnString = "({$whereString})";
		if ($idString != '' && $returnString != '') {
			$returnString = "({$idString}) OR {$returnString}";
		}
		else if ($idString != '') {
			$returnString = "{$idString}";
		}
		return Array($returnString, $parameters);
	}
}