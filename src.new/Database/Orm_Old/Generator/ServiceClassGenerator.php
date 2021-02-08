<?php
namespace LynkCMS\Component\Database\Orm\Generator;

class ServiceClassGenerator {
	protected $templateRoot;
	protected $entityRoot;
	protected $entityNamespace;
	protected $name;
	protected $config;
	public function __construct($entityRoot, $entityNamespace, $name, $configOrFile) {
		$this->templateRoot = __DIR__ . '/Templates';
		$this->entityRoot = rtrim($entityRoot, '/');
		$this->entityNamespace = rtrim($entityNamespace, '\\');
		$this->name = $name;
		if (!is_array($configOrFile) && file_exists($configOrFile)) {
			if (preg_match('/\.json$/', $configOrFile)) {
				$fileContents = file_get_contents($configOrFile);
				$this->config = json_decode($fileContents, true);
			}
			else {
				$this->config = include $configOrFile;
			}
		}
		else
			$this->config = $configOrFile;
	}
	public function defaultConfig($merge) {
		$config = array_merge(Array(
			'table' => 'TABLE_NOT_SET'
			,'alias' => 'this'
			,'primaryKey' => null
			,'idStrategy' => 'auto' //--auto,none,uuid
			,'groupBy' => null
			,'columns' => Array()
			,'joins' => Array()
			,'baseService' => 'LynkCMS\\Component\\Database\\Orm\\BaseService'
		), $merge);
		$columns = Array();
		foreach ($config['columns'] as $columnKey => $columnConfig) {
			if (is_numeric($columnKey)) {
				$columnKey = $columnConfig;
				$columnConfig = Array();
			}
			$columnConfig = array_merge(Array(
				'get' => null
				,'set' => null
				,'default' => null
				,'alias' => null
				,'required' => false //--not implemented
				,'type' => 'varchar' //--not implemented
				,'length' => 0 //--not implemented
			), $columnConfig);
			$columns[$columnKey] = $columnConfig;
		}
		$config['columns'] = $columns;
		return $config;
	}
	public function generateClass() {
		$config = $this->defaultConfig($this->config);
		$table = $config['table'];
		$alias = $config['alias'];
		$primaryKey = $config['primaryKey'];
		$idStrategy = $config['idStrategy'];
		$columns = $config['columns'];
		$baseService = $config['baseService'];
		if (!$primaryKey) {
			echo "ServiceClassGenerator::error: no primary key for {$this->name}\n";
			exit;
		}
		if (is_string($primaryKey) && strpos($primaryKey, ',') !== false) {
			$primaryKey = explode(',', $primaryKey);
		}

		$vars1 = Array();
		$vars1['namespaceRoot'] = "{$this->entityNamespace}\\Service\\Base";
		$vars1['baseService'] = $baseService;
		$vars1['entityClass'] = "{$this->entityNamespace}\\Base\\{$this->name}";
		$vars1['className'] = $this->name;
		$vars1['tableName'] = $table;
		$vars1['tableAlias'] = $alias;
		$vars1['primaryKey'] = is_array($primaryKey)
			? "Array('" . (implode("', '", $primaryKey)) . "')"
			: "'{$primaryKey}'";
		$this->generateClassJoins($config, $vars1);
		$this->generateClassGroupBy($config, $vars1);
		$this->generateClassMaps($config, $vars1);

		$vars2 = Array();
		$vars2['namespaceRoot'] = "{$this->entityNamespace}\\Service";
		$vars2['baseService'] = "{$this->entityNamespace}\\Service\\Base\\{$this->name}";
		$vars2['entityClass'] = "{$this->entityNamespace}\\Base\\{$this->name}";
		$vars2['className'] = $this->name;

		
		// $groupby = isset($this->settings['groupBy']) ? "\n\t\t\$this->orm->groupBy('" . $this->settings['groupBy'] ."');" : '';
		
		// $joinIds = $joinIdsVar = $joins = "";
		// if (isset($this->settings['joins'])) {
		// 	foreach ($this->settings['joins'] as $jKey => $jVal) {
		// 		for ($i = 0; $i < sizeof($jVal); $i++) {
		// 			$joinMapParts = explode('.', $jVal[$i]['tableMap']);
		// 			$map .= "\t\t\t'{$joinMapParts[0]}' => '{$joinMapParts[1]}',\n";
		// 			$bindingParts1 = explode('.', explode('=', $jVal[$i]['binding'])[0]);
		// 			$bindingParts2 = explode('.', explode('=', $jVal[$i]['binding'])[1]);
		// 			switch ($jVal[$i]['type']) {
		// 				case 'always':
		// 					$joins .= "\t\t\$this->orm->{$jKey}('{$joinMapParts[0]}', '{$jVal[$i]['binding']}');\n";
		// 					break;
		// 				case 'param':
		// 					$joinIds .= ", \${$bindingParts1[1]} = null";
		// 					$joinIdsVar .= ", \${$bindingParts1[1]}";
		// 					$joins .= "\t\tif (\${$bindingParts1[1]} !== null) {\n";
		// 					$joins .= "\t\t\t\$this->orm->{$jKey}('{$joinMapParts[0]}', '{$jVal[$i]['binding']}');\n";
		// 					$joins .= "\t\t\t\$joinWheres[] = \"{$bindingParts1[0]}.{$bindingParts1[1]}=:{$bindingParts1[1]}_param\";\n";
		// 					$joins .= "\t\t\t\$ids['$bindingParts1[1]_param'] = \${$bindingParts1[1]};\n";
		// 					$joins .= "\t\t}\n";
		// 					break;
		// 				case 'search':
		// 					$joins .= "\t\tif (\$this->needsJoin(\$orgConstraintSize, '{$joinMapParts[0]}', \$o))\n";
		// 					$joins .= "\t\t\t\$this->orm->{$jKey}('{$joinMapParts[0]}', '{$jVal[$i]['binding']}');\n";
		// 					break;
		// 				case 'search-param':
		// 					$joinIds .= ", \${$bindingParts1[1]} = null";
		// 					$joinIdsVar .= ", \${$bindingParts1[1]}";
		// 					$joins .= "\t\tif (\${$bindingParts1[1]} !== null || \$this->needsJoin(\$orgConstraintSize, '{$joinMapParts[0]}', \$o))\n";
		// 					$joins .= "\t\t\t\$this->orm->{$jKey}('{$joinMapParts[0]}', '{$jVal[$i]['binding']}');\n";
		// 					$joins .= "\t\tif (\${$bindingParts1[1]} !== null) {\n";
		// 					$joins .= "\t\t\t\$joinWheres[] = \"{$bindingParts1[0]}.{$bindingParts1[1]}=:{$bindingParts1[1]}_param\";\n";
		// 					$joins .= "\t\t\t\$ids['$bindingParts1[1]_param'] = \${$bindingParts1[1]};\n";
		// 					$joins .= "\t\t}\n";
		// 					break;
		// 			}
		// 		}
		// 	}
		// }

		// $uniqueIdField = 'getUid';
		// for ($i = 0; $i < sizeof($this->settings['fields']); $i++) {
		// 	$nameParts = EntityClassGenerator::getNamedField($this->settings['fields'][$i]);
		// 	if ($this->settings['idfield'] == $nameParts[0])
		// 		$uniqueIdField = $nameParts;
		// }

		$removeDuplicateNewlines = function($str) {
			$str = str_replace("\r\n", "\n", $str);
			while (strpos($str, "\n\n") !== false) {
				$str = str_replace("\n\n", "\n", $str);
			}
			return $str;
		};

		$output = file_get_contents($this->templateRoot . '/baseService_class.php');
		foreach ($vars1 as $k => $v) {
			if ($v !== null && !is_object($v) && !is_array($v))
				$output = str_replace("%{$k}%", $removeDuplicateNewlines($v), $output);
		}
		$output = $removeDuplicateNewlines($output);
		$output = str_replace('{NL}', '', $output);
		// $output = str_replace('%namespaceRoot%', $this->namespaceRoot . '\\Base', $output);
		// $output = str_replace('%entityClass%', $this->entityClass, $output);
		// $output = str_replace('%baseService%', $this->settings['baseService'], $output);
		// $output = str_replace('%className%', $this->serviceName, $output);
		// $output = str_replace('%mappings%', rtrim($map, ",\n"), $output);
		// $output = str_replace('%joinIds%', rtrim($joinIds, ','), $output);
		// $output = str_replace('%joinIdsVar%', rtrim($joinIdsVar, ','), $output);
		// $output = str_replace('%tableMap%', $mapParts[0], $output);
		// $output = str_replace('%joins%', rtrim($joins), $output);
		// $output = str_replace('%groupby%', $groupby, $output);
		// $output = str_replace('%idfield%', $this->settings['idField'], $output);
		// $output = str_replace('%uidFunction%', $uniqueIdField[1], $output);
		// $output = str_replace('%uidName%', $uniqueIdField[0], $output);

		$output2 = file_get_contents($this->templateRoot . '/service_class.php');
		foreach ($vars2 as $k => $v) {
			if ($v && !is_object($v) && !is_array($v))
				$output2 = str_replace("%{$k}%", $removeDuplicateNewlines($v), $output2);
		}
		$output2 = $removeDuplicateNewlines($output2);
		$output2 = str_replace('{NL}', '', $output2);
		// $output2 = str_replace('%namespaceRoot%', $this->namespaceRoot, $output2);
		// $output2 = str_replace('%entityClass%', $this->entityClass, $output2);
		// $output2 = str_replace('%baseService%', $this->namespaceRoot . '\\Base\\' . $this->serviceName, $output2);
		// $output2 = str_replace('%className%', $this->serviceName, $output2);

		// $unsetIdOnInsert = (
		// 	!isset($this->settings['idAutoIncrement']) ||
		// 	(isset($this->settings['idAutoIncrement']) && $this->settings['idAutoIncrement'] == true)
		// );
		// $output = str_replace('%idUnset%', ($unsetIdOnInsert ? 'true' : 'false'), $output);


		if (!file_exists("{$this->entityRoot}/Service/"))
			mkdir("{$this->entityRoot}/Service/", 0755, true);
		if (!file_exists("{$this->entityRoot}/Service/Base/"))
			mkdir("{$this->entityRoot}/Service/Base/", 0755, true);
		file_put_contents("{$this->entityRoot}/Service/Base/{$this->name}.php", $output);
		// if (!file_exists("{$this->entityRoot}/Service/{$this->name}.php"))
			file_put_contents("{$this->entityRoot}/Service/{$this->name}.php", $output2);
	}

	protected function generateClassJoins($config, &$vars) {
		$joinStr = '';
		$jcounter = 0;
		foreach ($config['joins'] as $joinType => $joins) {
			$joinPrefix = 'j';
			foreach ($joins as $join) {
				$jcounter++;
				$join = array_merge(Array(
					'table' => null
					,'what' => null
					,'on' => null
					,'parameter' => null
					,'include' => 'search'
				), $join);
				if (is_array($join['table'])) {
					$join['table'] = $join['table'];
				}
				else {
					$join['table'] = explode('.', $join['table']);
					$join['table'] = array_reverse($join['table']);
				}
				if (sizeof($join['table']) < 2) {
					$join['table'] = Array($join['table'], "{$joinPrefix}{$jcounter}");
				}
				$joinAdditionStr = "\$criteria['joins'][] = Array(Array('{$join['table'][0]}', '{$join['table'][1]}'),'{$join['what']}','{$join['on']}','{$joinType}');\n";
				switch ($join['include']) {
					case 'always':
						$joinStr .= "\t\t{$joinAdditionStr}\n";
					break;
					case 'param':
						if (!$join['parameter']) {
							$join['parameter'] = Array(
								ucfirst($this->toCamelCase($table[0]))
							);
						}
						else if (is_string($join['parameter'])) {
							if (strpos($join['parameter'], ' ') !== false) {
								$join['parameter'] = explode(' ', $join['parameter']);
							}
							else {
								$join['parameter'] = Array($join['parameter']);
							}
						}
						if (sizeof($join['parameter']) < 2)
							$join['parameter'][] = "{$join['on']}";
						if (strpos($join['parameter'][1], '.') === false)
							$join['parameter'][1] = "{$join['table'][1]}.{$join['parameter'][1]}";
						$joinStr .=
							"\t\tif (isset(\$criteria['{$join['parameter'][0]}'])) {\n"
							."\t\t\t{$joinAdditionStr}\n"
							."\t\t\t\$criteria['{$join['parameter'][1]}'] = \$criteria['{$join['parameter'][0]}'];\n"
							."\t\t\tunset(\$criteria['{$join['parameter'][0]}']);\n"
							."\t\t}\n";
					break;
					case 'search':
						$joinStr .= 
							"\t\t\$searchCriteria = array_merge(\$criteria['select'], \$criteria['where'], \$criteria['orWhere'], \$criteria['order']);\n"
							."\t\t\$searchResult = \$this->needsJoin('{$join['table'][1]}.', \$searchCriteria) || \$this->needsJoin('{$join['table'][0]}.', \$searchCriteria);\n";
						$joinStr .=
							"\t\tif (\$searchResult) {\n"
							."\t\t\t{$joinAdditionStr}\n"
							."\t\t}\n";
					break;
					case 'search-param':
						$joinStr .= 
							"\t\t\$searchCriteria = array_merge(\$criteria['select'], \$criteria['where'], \$criteria['orWhere'], \$criteria['order']);\n"
							."\t\t\$searchResult = \$this->needsJoin('{$join['table'][1]}.', \$searchCriteria) || \$this->needsJoin('{$join['table'][0]}.', \$searchCriteria);\n";
						if (!$join['parameter']) {
							$join['parameter'] = Array(
								ucfirst($this->toCamelCase($table[0]))
							);
						}
						else if (is_string($join['parameter'])) {
							if (strpos($join['parameter'], ' ') !== false) {
								$join['parameter'] = explode(' ', $join['parameter']);
							}
							else {
								$join['parameter'] = Array($join['parameter']);
							}
						}
						if (sizeof($join['parameter']) < 2)
							$join['parameter'][] = "{$join['on']}";
						if (strpos($join['parameter'][1], '.') === false)
							$join['parameter'][1] = "{$join['table'][1]}.{$join['parameter'][1]}";
						$joinStr .=
							"\t\tif (isset(\$criteria['{$join['parameter'][0]}']) || \$searchResult) {\n"
							."\t\t\t{$joinAdditionStr}\n"
							."\t\t\tif (isset(\$criteria['{$join['parameter'][0]}'])) {\n"
							."\t\t\t\t\$criteria['{$join['parameter'][1]}'] = \$criteria['{$join['parameter'][0]}'];\n"
							."\t\t\t\tunset(\$criteria['{$join['parameter'][0]}']);\n"
							."\t\t\t}\n"
							."\t\t}\n";
					break;
					default:
						# code...
						break;
				}
			}
		}
		$vars['joins'] = $joinStr;
	}

	protected function generateClassGroupBy($config, &$vars) {
		if ($config['groupBy']) {
			$vars['groupBy'] = "\t\tif (!isset(\$mod['group']) || !\$mod['group']) {\n";
			$vars['groupBy'] .= "\t\t\t\$mod['group'] = " . (is_array($config['groupBy'])
				? "Array('" . (implode("', '", $vars['groupBy'])) . "');"
				: "'{$config['groupBy']}'"
			);
			$vars['groupBy'] .= ";\n\t\t}";
		}
		else
			$vars['groupBy'] = '';
	}

	protected function generateClassMaps($config, &$vars) {
		$map = "";
		foreach ($config['columns'] as $columnKey => $columnConfig) {
			$columnNames = $this->columnNames($columnKey);
			$columnNames[0] = ucfirst($columnNames[0]);
			$map .= "\t\t\t'{$columnNames[0]}' => '{$columnKey}',\n";
		}
		if ($map != '')
			$map = rtrim($map, ",\n");
		$vars['entityColumnMap'] = $map;
	}

	protected function columnNames($string) {
		$variableName = $functionName = '';
		if (strpos($string, '.') !== false) {
			$stringParts = explode('.', $string);
			$variableName = $this->toCamelCase($stringParts[0]);
			$functionName = $stringParts[1];
		}
		else {
			$variableName = $this->toCamelCase($string);
			$functionName = ucfirst($variableName);
		}
		return [$variableName, $functionName];
	}

	protected function toCamelCase($string) {
		$parts = explode('_', $string);
		$string = array_shift($parts);
		while (sizeof($parts)) {
			$string .= ucfirst($parts[0]);
			array_shift($parts);
		}
		return $string;
	}
	protected function toProperCase($string) {
		return ucfirst($this->toCamelCase($string));
	}
}