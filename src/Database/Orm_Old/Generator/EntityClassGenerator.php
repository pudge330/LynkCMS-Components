<?php
namespace BGStudios\Component\Database\Orm\Generator;

class EntityClassGenerator {
	protected $templateRoot;
	protected $entityRoot;
	protected $entityNamespace;
	protected $name;
	protected $config;
	public function __construct($entityRoot, $entityNamespace, $name, $configOrFile) {
		$this->templateRoot = __DIR__ . '/Templates';
		$this->entityRoot = rtrim($entityRoot, '/');
		$this->entityNamespace = $entityNamespace;
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
			,'columns'
			,'joins' => Array()
			,'baseEntity' => 'BGStudios\\Component\\Database\\Orm\\BaseEntity'
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
		$baseEntity = $config['baseEntity'];
		if (!$primaryKey) {
			echo "EntityClassGenerator::error: no primary key for {$this->name}\n";
			exit;
		}
		if (is_string($primaryKey) && strpos($primaryKey, ',') != false) {
			$primaryKey = explode(',', $primaryKey);
		}
		else if (is_string($primaryKey)) {
			$primaryKey = Array($primaryKey);
		}
		$config['primaryKey'] = $primaryKey;

		$vars1 = Array();
		$vars1['namespaceRoot'] = "{$this->entityNamespace}\\Base";
		$vars1['baseEntity'] = $baseEntity;
		$vars1['className'] = $this->name;
		$this->generateClassAliasMap($config, $vars1);
		$this->generateClassDefaultValues($config, $vars1);
		$this->generateClassProperties($config, $vars1);
		$this->generateClassColumnMap($config, $vars1);
		$this->generateClassVariables($config, $vars1);
		$this->generateClassGettersSetters($config, $vars1);

		$vars2 = Array();
		$vars2['namespaceRoot'] = "{$this->entityNamespace}";
		$vars2['baseEntity'] = "{$this->entityNamespace}\\Base\\{$this->name}";
		$vars2['className'] = $this->name;


		$removeDuplicateNewlines = function($str) {
			$str = str_replace("\r\n", "\n", $str);
			while (strpos($str, "\n\n") !== false) {
				$str = str_replace("\n\n", "\n", $str);
			}
			return $str;
		};

		$output = file_get_contents($this->templateRoot . '/baseEntity_class.php');
		foreach ($vars1 as $k => $v) {
			if ($v !== null && !is_object($v) && !is_array($v))
				$output = str_replace("%{$k}%", $removeDuplicateNewlines($v), $output);
		}
		$output = $removeDuplicateNewlines($output);
		$output = str_replace('{NL}', '', $output);

		$output2 = file_get_contents($this->templateRoot . '/entity_class.php');
		foreach ($vars2 as $k => $v) {
			if ($v !== null && !is_object($v) && !is_array($v))
				$output2 = str_replace("%{$k}%", $removeDuplicateNewlines($v), $output2);
		}
		$output2 = $removeDuplicateNewlines($output2);
		$output2 = str_replace('{NL}', '', $output2);

		if (!file_exists($this->entityRoot))
			mkdir($this->entityRoot, 0755, true);
		if (!file_exists($this->entityRoot . '/Base/'))
			mkdir($this->entityRoot . '/Base/', 0755, true);
		file_put_contents("{$this->entityRoot}/Base/{$this->name}.php", $output);
		// if (!file_exists("{$this->entityRoot}/{$this->name}.php"))
			file_put_contents("{$this->entityRoot}/{$this->name}.php", $output2);
	}
	public function generateClassAliasMap($config, &$vars) {
		$map = '';
		foreach ($config['columns'] as $ckey => $cval) {
			if (is_array($cval) && isset($cval['alias'])) {
				if (!is_array($cval['alias'])) {
					if (strpos($cval['alias'], ' ') !== false) {
						$cval['alias'] = explode(' ', $cval['alias']);
					}
					else if (strpos($cval['alias'], ',') !== false) {
						$cval['alias'] = explode(',', $cval['alias']);
					}
					else {
						$cval['alias'] = Array($cval['alias']);
					}
				}
				$propName = ucfirst($this->toCamelCase($ckey));
				foreach ($cval['alias'] as $alias) {
					$map .= "'{$alias}' => '{$propName}', ";
				}
			}
		}
		if ($map != '') {
			$map = rtrim($map, ", ");
		}
		$vars['aliasMap'] = $map;
	}
	public function generateClassColumnMap($config, &$vars) {
		$map = '';
		foreach ($config['columns'] as $ckey => $cval) {
			$key = is_array($cval) ? $ckey : $cval;
			$value = $this->toProperCase($key);
			$map .= "\t\t\t'{$key}' => '{$value}',\n";
		}
		$map = rtrim($map, ",\n");
		$vars['columnMap'] = $map;
	}
	public function generateClassVariables($config, &$vars) {
		$str = '';
		foreach ($config['columns'] as $ckey => $cval) {
			$key = is_array($cval) ? $ckey : $cval;
			$value = $this->toCamelCase($key);
			$str .= "\tprotected \${$value};\n";
		}
		$vars['variables'] = $str;
	}
	public function generateClassDefaultValues($config, &$vars) {
		$str = '';
		foreach ($config['columns'] as $ckey => $cval) {
			if (is_array($cval) && isset($cval['default'])) {
				$value = $this->toCamelCase($ckey);
				$str .= "\t\t\$this->{$value} = {$cval['default']};\n";
			}
		}
		$vars['defaultValues'] = $str;
	}
	public function generateClassProperties($config, &$vars) {
		$str = '';
		foreach ($config['columns'] as $ckey => $cval) {
			$key = is_array($cval) ? $ckey : $cval;
			$value = $this->toCamelCase($key);
			$str .= "'{$value}', ";
		}
		$str = rtrim($str, ', ');
		$vars['properties'] = $str;
	}
	public function generateClassGettersSetters($config, &$vars) {
		$str = '';
		$primaryKey = $config['primaryKey'];
		foreach ($config['columns'] as $ckey => $cval) {
			if (is_array($cval) && isset($cval['get'])) {
				$funcName = $this->toProperCase($ckey);
				$propName = $this->toCamelCase($ckey);
				$str .= 
					"\tpublic function get{$funcName}(\$raw = false) {\n"
					."\t\treturn \$this->{$cval['get']}(\$this->{$propName}, \$raw);\n"
					."\t}\n";
			}
			else {
				if (in_array($ckey, $primaryKey)) {
					$funcName = $this->toProperCase($ckey);
					$propName = $this->toCamelCase($ckey);
					$str .= 
						"\tpublic function get{$funcName}(\$raw = false) {\n"
						."\t\t\${$propName} = \$this->{$propName};\n"
						."\t\tif (!\${$propName} || \${$propName} == '') {\n"
						."\t\t\treturn null;\n"
						."\t\t}\n"
						."\t\treturn \${$propName};\n"
						."\t}\n";
				}
			}
			if (is_array($cval) && isset($cval['set'])) {
				$funcName = $this->toProperCase($ckey);
				$propName = $this->toCamelCase($ckey);
				$str .= 
					"\tpublic function set{$funcName}(\${$propName}) {\n"
					."\t\t\$this->{$propName} = \$this->{$cval['set']}(\${$propName});\n"
					."\t\treturn \$this;\n"
					."\t}\n";
			}
			else {
				
			}
		}
		$vars['gettersSetters'] = $str;
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