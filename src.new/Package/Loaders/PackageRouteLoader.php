<?php
namespace LynkCMS\Component\Package\Loaders;

use LynkCMS\Component\Config\Config;
use LynkCMS\Component\Package\PackagePaths;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class PackageRouteLoader {
	private $cacheDir;
	private $pkgs;
	private $config;

	public function __construct(PackagePaths $p, $cdir, $config = null) {
		$this->cacheDir = $cdir;
		$this->pkgs = $p;
		$this->config = $config instanceof Config ? $config : new Config();
	}

	public function loadCollection($name, $cfile) {
		$tmpCollection = null;

		$cacheFile = $this->cacheDir . "{$name}_{$cfile}.object";
		if (file_exists($cacheFile)) {
			$tmpCollection = unserialize(file_get_contents($cacheFile));
		}
		else {
			$file = $this->pkgs->config($name, $cfile);
			if (file_exists($file)) {
				$routing = $this->config->get($file);

				$tmpCollection = new RouteCollection();
				if ($routing) {
					foreach ($routing as $rkey => $rvalues)
						$tmpCollection->add($rkey, static::buildRoute($rvalues));
				}

				if (!is_dir($this->cacheDir))
					mkdir($this->cacheDir, 0755, true);

				file_put_contents($cacheFile, serialize($tmpCollection));
			}
		}
		return $tmpCollection;
	}

	public static function buildRoute($routeValues) {
		$r_params = [
			'path' => '',
			'defaults' => [],
			'requirements' => [],
			'options' => [],
			'host' => '',
			'schemes' => [],
			'methods' => []
		];
		$r_params = array_values(array_merge($r_params, $routeValues));
		
		return new Route($r_params[0], $r_params[1], $r_params[2], $r_params[3], $r_params[4], $r_params[5], $r_params[6]);
	}
}