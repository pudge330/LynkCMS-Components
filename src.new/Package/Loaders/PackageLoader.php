<?php
namespace LynkCMS\Component\Package\Loaders;

use LynkCMS\Component\Config\Config;

class PackageLoader {
	private $config;
	private $root;
	private $c;
	public function __construct($cfg, $config = null) {
		$this->config = $cfg;
		$this->c = $config instanceof Config ? $config : new Config();
	}
	public function load($container) {
		$outputGlobals = $pkgObjects = $pkgNames = [];
		foreach ($this->config as $cKey => &$cVal) {
			$cVal = $this->parseConfig($cVal);
			if ($cVal['enabled']) {
				$class = new $cVal['class']();

				$cVal['version'] = $class->getVersion();
				$cVal['namespace'] = $class->getNamespaceRoot();
				$cVal['root'] = $class->getPackageRoot();

				$pkgObjects[] = $class;
				$pkgNames[] = $class->getName();

				$context = $class->doContext($container);
				$outputGlobals = array_merge($outputGlobals, $context);

				$container["lynk.packages.{$class->getName()}"] = $class;
			}
		}
		$container['lynk.packages'] = $pkgNames;
		foreach ($pkgObjects as $obj) {
			$obj->doServices($container);
			$obj->doRoutes($container);
		}
		foreach ($pkgObjects as $obj) {
			$obj->doInit($container);
		}
		$container['lynk.outputGlobals']->merge($outputGlobals);
	}
	private function parseConfig($cfg) {
		$c = [
			'enabled' => true,
			'locked' => false
		];
		$cfg = array_merge($c, $cfg);
		return $cfg;
	}
}