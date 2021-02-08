<?php
namespace BGStudios\Component\ApplicationCompiler\SubCompiler;

class AbstractCompiler {
	protected $root;
	protected $env;
	public function __construct($root, $env) {
		$this->root = $root;
		$this->env = $env;
	}
	public function compile($config, &$vars) {
	}
	public function configImport($key, &$import) {
	}
	public function config(&$config) {
	}
	public function injectionSetup($event, $config) {
	}
	public function injection($event, $config) {
	}
	public function serviceRegex($string) {
		$match = [];
		if (is_string($string) && preg_match('/^@([a-zA-Z0-9_]+)(.+)?$/', $string, $match)) {
			$serviceName = $match[1];
			$serviceCall = isset($match[2]) ? $match[2] : null;
			return [$serviceName, $serviceCall];
		}
		return [null, null];
	}
	public function parameterRegex($string) {
		$match = [];
		if (is_string($string) && preg_match('/^(?:.+)?(%[^%]+%)(?:.+)?$/', $string, $match)) {
			$paramMatch = [];
			preg_match('/^%([^\[]+)(.+)?%$/', $match[1], $paramMatch);
			$paramName = $paramMatch[1];
			$paramKeys = isset($paramMatch[2]) ? $paramMatch[2] : null;
			$extraMatch = [];
			preg_match('/^(.+)?(?:%[^%]+%)(.+)?$/', $string, $extraMatch);
			$extra1 = isset($extraMatch[1]) ? $extraMatch[1] : null;
			$extra2 = isset($extraMatch[2]) ? $extraMatch[2] : null;
			return [$extra1, $extra2, $match[0], $paramName, $paramKeys];
		}
		return [null, null, null, null, null];
	}
	protected function processServiceName($key) {
		$copy = preg_replace('/([^a-zA-Z0-9_]+)/','{{SPACE}}', $key);
		$copy = explode('{{SPACE}}', $copy);
		foreach ($copy as $index => $tcopy)
			$copy[$index] = ucfirst($tcopy);
		$copy = implode('', $copy);
		return $copy;
	}
	protected function startBuffer() {
		ob_start();
	}
	protected function stopBuffer() {
		$c = ob_get_contents(); ob_end_clean(); return $c;
	}
}
