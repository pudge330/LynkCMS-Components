<?php
/**
 * This file is part of the LynkCMS Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS Components
 * @subpackage ApplicationCompiler
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\ApplicationCompiler\SubCompiler;

/**
 * Abstract sub-compiler class.
 */
class AbstractCompiler {

	/**
	 * @var string Root directory.
	 */
	protected $root;

	/**
	 * @var string Environment type.
	 */
	protected $env;

	/**
	 * @param string $root Root directory.
	 * @param string $env Environment type.
	 */
	public function __construct($root, $env) {
		$this->root = $root;
		$this->env = $env;
	}

	/**
	 * Compile handler.
	 * 
	 * @param Array $config Configuration.
	 * @param Array $vars Variables for template.
	 */
	public function compile($config, &$vars) {
	}

	/**
	 * Config import handler.
	 * 
	 * @param string $key Config top-level key.
	 * @param mixed $import Imported config.
	 */
	public function configImport($key, &$import) {
	}

	/**
	 * Config handler.
	 * 
	 * @param Array $config Configuration.
	 */
	public function config(&$config) {
	}

	/**
	 * Injection setup.
	 * 
	 * @param InjectionEvent $event Injection event instance.
	 * @param Array $config Configuration.
	 */
	public function injectionSetup($event, $config) {
	}

	/**
	 * File Injection.
	 * 
	 * @param InjectionEvent $event Injection event instance.
	 * @param Array $config Configuration.
	 */
	public function injection($event, $config) {
	}

	/**
	 * Configuration service regex.
	 * 
	 * @param string $string String to check.
	 * 
	 * @return Array Service name and service call if matched, null otherwise.
	 */
	public function serviceRegex($string) {
		$match = [];
		if (is_string($string) && preg_match('/^@([a-zA-Z0-9_]+)(.+)?$/', $string, $match)) {
			$serviceName = $match[1];
			$serviceCall = isset($match[2]) ? $match[2] : null;
			return [$serviceName, $serviceCall];
		}
		return [null, null];
	}

	/**
	 * Configuration parameter regex.
	 * 
	 * @param string $string String to check.
	 * 
	 * @return Array Parameter configuration.
	 */
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

	/**
	 * Process service key.
	 * 
	 * @param string $key Service name.
	 * 
	 * @return string Processed name.
	 */
	protected function processServiceName($key) {
		$copy = preg_replace('/([^a-zA-Z0-9_]+)/','{{SPACE}}', $key);
		$copy = explode('{{SPACE}}', $copy);
		foreach ($copy as $index => $tcopy)
			$copy[$index] = ucfirst($tcopy);
		$copy = implode('', $copy);
		return $copy;
	}

	/**
	 * Start output buffer.
	 */
	protected function startBuffer() {
		ob_start();
	}

	/**
	 * Stop output buffer and get output.
	 * 
	 * @return string Output.
	 */
	protected function stopBuffer() {
		$c = ob_get_contents();
		ob_end_clean();
		return $c;
	}
}
