<?php
namespace BGStudios\Component\Package;

class PathResolver {
	private $packages;
	private $packageNamesRegex;
	private $root;
	public function __construct(array $packages = [], $root = '/_/') {
		$this->packages = [];
		$this->root = $root;
		$this->setPackages($packages);
	}
	public function rootNamespace($p) {
		return isset($this->packages[$p]['namespace']) ? $this->packages[$p]['namespace'] : null;
	}
	public function packageRoot($p) {
		return \bgs\realRelPath(LYNK_ROOT . '/' . $this->packages[$p]['root']);
	}
	public function object($p) {
		return $this->packageRoot($p) . $p . LYNK_DS . $p . '.php';
	}
	public function root($p, $n = '') {
		return $this->packageRoot($p) . $p . LYNK_DS . $n;
	}
	public function assets($p, $n = '') {
		return $this->packageRoot($p) . $p . LYNK_DS . 'Resources' . LYNK_DS . 'assets' . LYNK_DS . $n;
	}
	public function config($p, $n = '') {
		return $this->packageRoot($p) . $p . LYNK_DS . 'Resources' . LYNK_DS . 'config' . LYNK_DS . $n;
	}
	public function data($p, $n = '') {
		return $this->packageRoot($p) . $p . LYNK_DS . 'Resources' . LYNK_DS . 'data' . LYNK_DS . $n;
	}
	public function scripts($p, $n = '') {
		return $this->packageRoot($p) . $p . LYNK_DS . 'Resources' . LYNK_DS . 'scripts' . LYNK_DS . $n;
	}
	public function styles($p, $n = '') {
		return $this->packageRoot($p) . $p . LYNK_DS . 'Resources' . LYNK_DS . 'styles' . LYNK_DS . $n;
	}
	public function views($p, $n = '') {
		return $this->packageRoot($p) . $p . LYNK_DS . 'Resources' . LYNK_DS . 'views' . LYNK_DS . $n;
	}
	public function resources($p, $n = '') {
		return $this->packageRoot($p) . $p . LYNK_DS . 'Resources' . LYNK_DS . $n;
	}
	public function resolve($path) {
		$bits = null;
		if (preg_match("/^@({$this->packageNamesRegex})(\:p)?$/", $path, $matches)) {
			$package = $matches[1];
			$public = isset($matches[2]) ? true : false;
			return ($public
				? rtrim($this->pub(null, $package), '/')
				: rtrim($this->root($package), '/')
			);
		}
		else if (preg_match("/^@({$this->packageNamesRegex})(\:p)?\/(.*)/", $path, $matches)) {
			$package = $matches[1];
			$public = $matches[2] != '' ? true : false;
			$remaining = $matches[3];
			return ($public
				? rtrim($this->pub(null, $package), '/')
				: rtrim($this->root($package), '/')
			) . "/{$remaining}";
		}
		else if (preg_match("/^%({$this->packageNamesRegex})(\:p)?%(.*)/", $path, $matches)) {
			$package = $matches[1];
			$public = $matches[2] != '' ? true : false;
			$remaining = $matches[3];
			return ($public
				? rtrim($this->pub(null, $package), '/')
				: rtrim($this->root($package), '/')
			) . "{$remaining}";
		}
		else {
			//--sub-folder specific support to be removed in later updates
			if (preg_match('/^%([a-zA-Z0-9]+)(?:(?::([pP]))?(?::)([a-zA-Z0-9]+))?%([^:]+)?$/', $path, $matches)) {
				//--%NamoOfPackage:foo%some-file.txt -- %NamoOfPackage:p:foo%some-file.txt
				$bits = $matches;
			}
			else if (preg_match('/^@([a-zA-Z0-9]+)(?:(?::([pP]))?(?::)([a-zA-Z0-9]+))?\/([^:]+)?$/', $path, $matches)) {
				//--@NameOfPackage:foo/some-file.txt -- @NameOfPackage:p:foo/some-file.txt
				$bits = $matches;
			}
			else if (preg_match('/^@([a-zA-Z0-9]+)(?:(?::([pP]))?(?::)([a-zA-Z0-9]+))?:([^:]+)?$/', $path, $matches)) {
				//--@NameOfPackage:foo:some-file.txt -- @NameOfPackage:p:foo:some-file.txt
				$bits = $matches;
			}
			if ($bits) {
				$package = $bits[1];
				$public = isset($bits[2]) && strtolower($bits[2]) == 'p' ? true : false;
				$type = isset($bits[3]) ? $bits[3] : null;
				$file = isset($bits[4]) ? $bits[4] : null;
				if ($public) {
					return $this->pub($type, $package, $file);
				}
				else {
					if (method_exists($this, strtolower($type)))
						return $this->{strtolower($type)}($package, $file);
					else
						return $path;
				}
			}
		}
		return $path;
	}
	public function pub($t, $p, $n = '') {
		//--tmp until all css & js path instances are fixed
		if ($t == 'js')
			$t = 'scripts';
		if ($t == 'css')
			$t = 'styles';
		$p = strtolower($p);
		$p = !$p || $p == '' ? '' : "/{$p}";
		$t = !$t || $t == '' ? '' : "/{$t}";
		$n = !$n || $n == '' ? '/' : "/{$n}";
		return "{$this->root}packages{$p}{$t}{$n}";
	}
	public function exists($p) {
		if (array_key_exists($p, $this->packages))
			return true;
		else
			return false;
	}
	public function setPackages($packages, $merge = true) {
		if ($merge) {
			$this->packages = array_merge($this->packages, $packages);
		}
		else {
			$this->packages = $packages;
		}
		$this->packageNamesRegex = '';
		foreach ($this->packages as $key => $config) {
			$this->packageNamesRegex .= ($this->packageNamesRegex != '' ? '|' : '') . $key;
		}
	}
	public function getPackages() {
		return $this->packages;
	}
}
