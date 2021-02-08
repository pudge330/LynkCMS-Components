<?php
namespace LynkCMS\Component\Mvc;

use Exception;

use LynkCMS\Component\Container\AbstractCompiledContainer;
use LynkCMS\Component\Container\ContainerAwareTrait;
use LynkCMS\Component\Storage\StandardContainer;
use LynkCMS\Component\Util\NamedBuffers;
use LynkCMS\Component\Container\Container;

class View extends Container {
	use ContainerAwareTrait;
	protected $data;
	protected $buffer;
	public function __construct(Array $values = [], AbstractCompiledContainer $compiledContainer = null) {
		parent::__construct($values, $compiledContainer);
		$this->data = [];
		$this->viewBuffer = new NamedBuffers();
	}
	public function buffer() {
		return $this->viewBuffer;
	}
	public function getData($key) {
		if ($this->hasData($key))
			return $this->data[$key];
		return null;
	}
	public function setData($key, $value = null) {
		if (is_array($key)) {
			$this->data = $value ? $key : array_merge($this->data, $key);
		}
		else
			$this->data[$key] = $value;
	}
	public function hasData($key) {
		return (array_key_exists($key, $this->data));
	}
	public function exportData() {
		return $this->data;
	}
	public function escape($val, $e = "UTF-8") {
		return htmlentities($val, ENT_QUOTES, $e);
	}
	public function cleanSlug($slug) {
		$slug = str_replace(' ', '-', $slug);
		$slug = preg_replace("/[^a-z0-9-]/i", "", iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug));
		$slug = str_replace('--', '-', $slug);
		return strtolower($slug);
	}
	public function attr($data, $prefix = null, $padding = true) {
		$attrString = '';
		foreach ($data as $k => $v)
			$attrString .= $v !== null ? " {$prefix}{$k}=\"{$v}\"" : " {$prefix}{$k}";
		if (!$padding && $attrString != '')
			$attrString = trim($attrString);
		return $attrString;
	}
	public function path($path) {
		if (strpos($path, '~') === 0)
			$path = $this->getService('dir.root') . $this->getService('dir.public') . "content/" . ltrim($path, '~');
		return $path;
	}
	public function renderPHP($path, $data = []) {
		$path = $this->path($path);
		if (is_array($data)) {
			$data = array_merge($this->exportData(), $data);
		}
		$view = $this;
		ob_start();
		$returned = include $path;
		$output = ob_get_contents();
		ob_end_clean();
		if (is_string($returned))
			$output .= $returned;
		return $output;
	}

	public function render() {
		$arguments = func_get_args();
		$argumentCount = sizeof($arguments);
		if (!$argumentCount) {
			throw new Exception('LynkCMS\\Component\\Mvc\\View Error: the render() method requires a path to a template.');
		}
		else {
			$path = $arguments[0];
			$data = $argumentCount > 1 ? $arguments[1] : Array();
			if (!$path) {
				throw new Exception('LynkCMS\\Component\\Mvc\\View Error: render() method $path argument cannot be blank');
			}
			return $this->renderPHP($path, $data);
		}
	}

	public function widget($name, $data = []) {
		$widgetPath = $this->path("~widgets/{$name}");
		if (file_exists($widgetPath . '.html.php'))
			return $this->render('php', $widgetPath . '.html.php', $data);
	}

	public function includeFile($path, $data = []) {
		$path = $this->path($path);
		if (!\bgs\startsWith($path, ROOT))
			$path = ROOT . ltrim($path, '/');
		return include $path;
	}

	public function includeFileRef($path, &$data = []) {
		$path = $this->path($path);
		if (!\bgs\startsWith($path, ROOT))
			$path = ROOT . ltrim($path, '/');
		return include $path;
	}
	public function url($route, $slugs = []) {
		$container = static::getContainer();
		try {
			return $container['router']->create($route, $slugs);
		}
		catch (Exception $e) {
			error_log("Caught {$e}");
			return '/';
		}
	}
	public function newUrl($route = '/', $slugs = []) {
		$container = static::getContainer();
		return $container['router']->createNew($route, $slugs);
	}
}