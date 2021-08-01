<?php
namespace BGStudios\Component\Package\Generator;

use LynkCMS\Component\Config\Config;

class PackageGenerator {
	protected $root;
	protected $templateDir;
	protected $config;
	public function __construct($root, $packageRoot, $config = null) {
		$this->config = $config instanceof Config ? $config : new Config();
		$this->root = $this->config->interpolate($root);
		$this->packageRoot = $this->config->interpolate($packageRoot);
		$this->templateDir = __DIR__ . '/Templates';
	}
	public function generate($name, $namespace, $files = true, $config = true) {
		$root = $this->root . '/' . $this->packageRoot;
		$shortname = str_replace('Package', '', $name);
		$pholders = [
			'pkg_name' => $name,
			'pkg_shortname' => $shortname,
			'pkg_lshortname' => strtolower($shortname),
			'created_time' => date('m/d/Y h:i:s A'),
			'namespace' => $namespace ? "{$namespace}\\" : ''
		];
		$folderConfig = $this->config->interpolate($this->folderConfig(), $pholders);
		$fileConfig = $this->config->interpolate($this->fileConfig(), $pholders);
		//--folders
		foreach ($folderConfig as $folder) {
			$fPath = $root . '/' . $name . LYNK_DS . $this->config->interpolate($folder, $pholders);
			if (!file_exists($fPath))
				mkdir($fPath, 0775, true);
		}
		//--files
		$create = Array('required' => true, 'package' => $files, 'config' => $config);
		$source = "{$this->templateDir}/";
		foreach ($fileConfig as $typeKey => $typeConfig) {
			if (array_key_exists($typeKey, $create) && $create[$typeKey]) {
				foreach ($typeConfig as $file) {
					$dest = $root . '/' . $name . LYNK_DS . $this->config->interpolate($file[1], $pholders);
					$template = file_get_contents("{$source}{$file[0]}");
					$template = $this->config->interpolate($template, $pholders);
					file_put_contents($dest, $template);
				}
			}
		}
		$packageConfig = Array(
			'class' => ($namespace ? "{$namespace}\\" : '') . "{$name}\\{$name}",
			'enabled' => true,
			'locked' => false
		);
		// if ($namespace && $namespace != '') {
			// $packageConfig = ['namespace' => $namespace] + $packageConfig;
		// }
		return $packageConfig;
	}
	public function folderConfig() {
		return Array(
			'Command'
			,'Controller'
			// ,'Entity'
			// ,'Form'
			// ,'Handler'
			// ,'Library'
			,'Resources'
			,'Resources/assets'
			,'Resources/config'
			// ,'Resources/data'
			,'Resources/scripts'
			,'Resources/styles'
			,'Resources/views'
			,'Resources/views/public'
			,'Service'
		);
	}
	public function fileConfig() {
		return Array(
			'required' => Array(
				'class' => Array(
					'packageObject_class.php'
					,'%pkg_name%.php'
				)
			)
			,'package' => Array(
				'command-base' => Array(
					'packageAbstractCommand_class.php'
					,'Command/AbstractCommand.php'
				)
				,'controller-base' => Array(
					'packageAbstractController_class.php'
					,'Controller/AbstractController.php'
				)
				,'service' => Array(
					'packageService_class.php'
					,'Service/%pkg_shortname%.php'
				)
				,'service-base' => Array(
					'packageAbstractService_class.php'
					,'Service/AbstractService.php'
				)
			)
			,'config' => Array(
				'config' => Array(
					'config.yml'
					,'Resources/config/config.yml'
				)
				,'webpage' => Array(
					'webpage_config.yml'
					,'Resources/config/document.yml'
				)
				,'public' => Array(
					'asset_config.yml'
					,'Resources/config/public.yml'
				)
			)
		);
	}
}