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
 * Application compiler compile event.
 */
class CompileEvent extends Event {

	/**
	 * @var string Event name.
	 */
	const NAME = 'build.compile';

	/**
	 * @var string Root directory.
	 */
	protected $root;

	/**
	 * @var string Environment type.
	 */
	protected $env;

	/**
	 * @var Array Application compiler configuration.
	 */
	protected $config;

	/**
	 * @var Array Variables for templates.
	 */
	protected $vars;

	/**
	 * @param string $root Root directory.
	 * @param string $env Environment type.
	 * @param Array $config Compiler configuration.
	 * @param Array $vars Variables for templates.
	 */
	public function __construct($root, $env, $config, $vars) {
		$this->root = $root;
		$this->env = $env;
		$this->config = $config;
		$this->vars = $vars;
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
	 * Get variables.
	 * 
	 * @return Array Variables.
	 */
	public function getVars() {
		return $this->vars;
	}

	/**
	 * Get variable.
	 * 
	 * @param string $key Variable name.
	 * 
	 * @return string Variable.
	 */
	public function getVar($key) {
		if (array_key_exists($key, $this->vars))
			return $this->vars[$key];
	}

	/**
	 * Set variables.
	 * 
	 * @param Array $vars Variables.
	 */
	public function setVars($vars) {
		$this->vars = array_merge($this->vars, $vars);
	}

	/**
	 * Set variable.
	 * 
	 * @param string $key Variable name.
	 * @param string $value Variable
	 */
	public function setVar($key, $value) {
		$this->vars[$key] = $value;
	}

	/**
	 * Append to variable.
	 * 
	 * @param string $var Variable name.
	 * @param string $append Variable value.
	 */
	public function appendVar($var, $append) {
		if (array_key_exists($var, $this->vars)) {
			$this->vars[$var] .= $append;
		}
	}
}