<?php
namespace %namespaceRoot%;
{NL}
use Exception;
use PDO;
use %baseService% as BaseService;
use %entityClass% as ObjectEntity;
{NL}
class %className% extends BaseService {
	public function __construct($connection) {
		parent::__construct($connection);
		$this->table = '%tableName%';
		$this->alias = '%tableAlias%';
		$this->primaryKey = %primaryKey%;
	}
	public function find($criteria = Array(), $mod = Array()) {
		if (is_numeric($criteria) || is_string($criteria)) {
			$criteria = Array(
				'criteria' => Array(
					$this->primaryKey => $criteria
				)
				,'idColumn' => $this->primaryKey
			);
			$mod = array_merge($mod, Array('limit' => 1));
			return $this->find($criteria, $mod);
		}
		$criteria = array_merge(Array('alias' => null, 'joins' => Array(), 'select' => Array(), 'where' => Array(), 'orWhere' => Array()), $criteria);
		$alias = $criteria['alias'] ?: $this->alias;
		$mod = array_merge(Array('order' => Array(), 'scalar' => false, 'limit' => null), $mod);
		$isSqlFnSelect = is_string($criteria['select'])
			? preg_match('/\s*(avg|count|max|min|sum)\s*\([\w\.*]+\)/', strtolower($criteria['select']))
			: false;
		if (!is_array($criteria['select']))
			$criteria['select'] = [$criteria['select']];
		$criteria['select'] = sizeof($criteria['select']) ? $criteria['select'] : Array("{$alias}.*");
		$isScalar = isset($mod['scalar']) && $mod['scalar'] ? true : false;
		$mod['scalar'] = true;
%joins%
%groupBy%
		$result = $this->dbService->find($this->table, $criteria, $mod);
		$result = $result && $mod['limit'] == 1 ? [$result] : $result;
		if ($result && !$isScalar && !$isSqlFnSelect) {
			$result = ObjectEntity::hydrate($result);
		}
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
	protected static function entityColumnMap() { 
		return Array(
%entityColumnMap%
		);
	}
}