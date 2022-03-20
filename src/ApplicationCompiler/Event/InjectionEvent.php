<?php
/**
 * This file is part of the Lynk Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Lynk Components
 * @subpackage ApplicationCompiler
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\ApplicationCompiler\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Application compiler injection event.
 */
class InjectionEvent extends Event {

	/**
	 * @var string Event name.
	 */
	const NAME = 'build.injection';

	/**
	 * @var string Root directory.
	 */
	protected $root;

	/**
	 * @var string Environment type.
	 */
	protected $env;

	/**
	 * @var Array Injection list.
	 */
	protected $injections;

	/**
	 * @param string $root Root directory.
	 * @param string $env Environment type.
	 * @param Array $config Compiler configuration.
	 * @param Array $injections Injections for templates.
	 */
	public function __construct($root, $env , $config, $injections = Array()) {
		$this->root = $root;
		$this->env = $env;
		$this->config = $config;
		$this->injections = $injections;
		foreach ($this->injections as $key => $val) {
			if (!is_array($val)) {
				$this->injections[$key] = Array();
				$this->injections[$key]['placeholder'] = $val;
				$this->injections[$key]['content'] = '';
			}
			else if (!isset($val['content'])) {
				$this->injections[$key]['content'] = '';
			}
		}
	}

	/**
	 * Get root directory.
	 * 
	 * @return string Root directory.
	 */
	public function getRoot() {
		return $this->root;
	}

	/**
	 * Get environment.
	 * 
	 * @return string Environment.
	 */
	public function getEnv() {
		return $this->env;
	}

	/**
	 * Get config.
	 * 
	 * @return Array Configuration.
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * Add file injection.
	 * 
	 * @param string $key Injection name.
	 * @param string $placeholder Placeholder name.
	 * @param string $content Injection content.
	 */
	public function addInjection($key, $placeholder, $content = '') {
		$this->injections[$key] = Array('placeholder' => $placeholder, 'content' => $content);
	}

	/**
	 * Get injections list.
	 * 
	 * @return Array File injections.
	 */
	public function getInjections() {
		return $this->injections;
	}

	/**
	 * Get injection content.
	 * 
	 * @param string $key Injection name.
	 * 
	 * @return string Injection content.
	 */
	public function getOutput($key) {
		if ($this->hasInjection($key))
			return $this->injections[$key]['content'];
	}

	/**
	 * Set injection content.
	 * 
	 * @param string $key Injection name.
	 * @param string $injection Injection content.
	 */
	public function setOutput($key, $injection) {
		$this->injections[$key]['content'] = $injection;
	}

	/**
	 * Append injection content.
	 * 
	 * @param string $key Injection name.
	 * @param string $injection Injection content.
	 */
	public function appendOutput($key, $injection) {
		if ($this->hasInjection($key))
			$this->injections[$key]['content'] .= $injection;
	}

	/**
	 * Check if injection exists.
	 * 
	 * @param string $key Injection name.
	 * 
	 * @return bool True if injection exists, false otherwise.
	 */
	public function hasInjection($key) {
		return array_key_exists($key, $this->injections);
	}
}