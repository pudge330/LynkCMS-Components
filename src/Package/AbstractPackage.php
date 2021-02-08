<?php
namespace BGStudios\Component\Package;

use ReflectionClass;
use BGStudios\Component\Package\Loaders\PackageRouteLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class AbstractPackage {
	public static $vendors;
	protected $name;
	protected $packageRoot;
	protected $namespaceRoot;
	protected $pathResolver;
	public function __construct() {
		if (!self::$vendors) {
			self::$vendors = [
				[LYNK_ROOT . LYNK_DS . LYNK_APP . '/vendor', LYNK_APP . '/vendor'],
				[LYNK_ROOT . LYNK_DS . 'vendor', 'vendor'],
				[LYNK_ROOT . LYNK_DS . LYNK_SRC, LYNK_SRC]
			];
		}
		$r = new ReflectionClass($this);
		$this->name = $r->getShortName();
		$this->packageRoot = $this->getRelativeRoot(substr(dirname($r->getFileName()), 0, -1 * strlen($this->name)));
		$this->namespaceRoot = substr($r->getNamespaceName(), 0, -1 * strlen($this->name));
		$this->namespaceRoot = rtrim($this->namespaceRoot, '\\');
		$this->pathResolver = new PathResolver([
			$this->name => [
				'root' => $this->packageRoot,
				'namespace' => $this->namespaceRoot
			]
		]);
	}
	public function doContext($container) {
		$context = $this->context($container);
		if (is_array($context)) {
			return $context;
		}
		return [];
	}
	public function doInit($container) {
		$this->init($container);
	}
	public function doServices($container) {
		$this->services($container);
	}
	public function doRoutes($container) {
		$routes = $this->routes($container);
		if ($routes !== null) {
			if ($routes instanceof RouteCollection) {
				$container->get('lynk.routeCollection')->addCollection();
			}
			else if (is_array($routes) && sizeof($routes)) {
				$collection = new RouteCollection();
				foreach ($routes as $key => $route) {
					$route = $route instanceof Route
						? $route
						: PackageRouteLoader::buildRoute($route);
					$collection->add($key, $route);
				}
				$container['lynk.routeCollection']->addCollection($collection);
			}
			else {
				//--for some reason this is  required as a fallback, no error message given just white screen
				// ?not needed anymore
				// $container['lynk.routeCollection']->addCollection(new RouteCollection());
			}
		}
	}
	public function getVersion() {
		return '1.0';
	}
	public function getName() {
		return $this->name;
	}
	public function getNamespaceRoot() {
		return $this->namespaceRoot;
	}
	public function getPackageRoot() {
		return $this->packageRoot;
	}
	public function getRelativeRoot($packageRoot, $vendors = null) {
		$vendors = $vendors ?: self::$vendors;
		$vendors = is_array($vendors) ? $vendors : [];
		$packageRoot = rtrim($packageRoot, DIRECTORY_SEPARATOR);
		foreach ($vendors as $vendor) {
			$vendor[0] = rtrim($vendor[0], DIRECTORY_SEPARATOR);
			$vendor[1] = rtrim($vendor[1], DIRECTORY_SEPARATOR);
			$link = is_link($vendor[0]) ? readlink($vendor[0]) : null;
			if ($link && \bgs\startsWith($packageRoot, $link)) {
				$relRoot = preg_replace('#^' . preg_quote($link) . '#', '', $packageRoot);
				$relRoot = trim($relRoot, DIRECTORY_SEPARATOR);
				$relRoot = ($vendor[1] ? $vendor[1] . DIRECTORY_SEPARATOR : '') . $relRoot . ($relRoot ? DIRECTORY_SEPARATOR : '');
				return $relRoot;
			}
			else if (\bgs\startsWith($packageRoot, $vendor[0])) {
				$relRoot = preg_replace('#^' . $vendor[0] . '#', '', $packageRoot);
				$relRoot = trim($relRoot, DIRECTORY_SEPARATOR);
				$relRoot = ($vendor[1] ? $vendor[1] . DIRECTORY_SEPARATOR : '') . $relRoot . ($relRoot ? DIRECTORY_SEPARATOR : '');
				return $relRoot;
			}
		}
		return $packageRoot;
	}
	protected function context($container) {}
	protected function services($container) {}
	protected function routes($container) {}
	protected function init($container) {}
	public function publicSymlinks() {
		return ['assets', 'styles', 'scripts', 'views', 'widgets'];
	}
	public function applicationBuildSubscribers() {
		return Array();
	}
	public function applicationBuildListeners() {
		return Array();
	}
	public function applicationBuildSubCompilers($root, $env) {
		return Array();
	}
	public function applicationBuildConfigMutator($app, $package) {
		return Array($app, $package);
	}
	public function applicationBuildFileInjection() {
		return '';
	}
}