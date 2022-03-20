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
 * Application compiler config event.
 */
class ConfigEvent extends Event {

	/**
	 * @var string Event name.
	 */
	const NAME = 'build.config';

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
	 * @param string $root Root directory.
	 * @param string $env Environment type.
	 * @param Array $config Compiler configuration.
	 */
	public function __construct($root, $env, $config) {
		$this->root = $root;
		$this->env = $env;
		$this->config = $config;
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
	 * Set config.
	 * 
	 * @param Array $config Configuration.
	 */
	public function setConfig($config) {
		$this->config = $config;
	}

	/**
	 * Merge config.
	 * 
	 * @param Array $config Configuration.
	 */
	public function mergeConfig($config) {
		$this->config = \lynk\deepMerge($this->config, $config);
	}
}