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
 * @subpackage Config
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Config;

/**
 * Config array access class. Allows retrieving config values from nested array using dot notation.
 * eg. 'site.domain'
 *
 * @todo Change the way data is handled. Store in original array, then deconstruct 'key' to loop through nested array and retrieve value.
 */
class ConfigAccess {

	/**
	 * @var Array The configuration.
	 */
	protected $config;

	/**
	 * @var Config Instance of the LynkCMS config class.
	 */
	protected $configService;

	/**
	 * @param Array $config Optional. The configuration.
	 * @param Config $c Optional. Instance of the LynkCMS config class.
	 */
	public function __construct($config = [], $c = null) {
		$this->config = [];
		$this->configService = $c instanceof Config ? $c : new Config();
		$this->addConfig($config);
	}

	/**
	 * Get a value from the config.
	 *
	 * @param string The config value key in dot notation.
	 * 
	 * @return mixed The config value.
	 */
	public function get($k) {
		if (isset($this->config[$k]))
			return $this->config[$k];
	}

	/**
	 * Set config value.
	 *
	 * @param string $k The config key.
	 * @param mixed $v The config value.
	 * 
	 * @return $this Current instance for method chaining.
	 */
	public function set($k, $v) {
		$this->config[$k] = $v;
		//--WILL DECONSTRUCT THE KEY, SPLIT AT '.' AND THEN LOOKUP VALUE FROM THEIR. SO FUTURE VERSION
		//--WILL ACCEPT ARRAY TO MERGE IN AS WELL AS KEY/VALUE PAIR.
		//
		//--need to find way to all points to config value
		//--maybe rebuild nested array and merge into config with addConfig()
		//--or loop backward through keys segments, build sub array and merge
		// $tmpKey = trim($k, '.');
		// $subKey = null;
		// while(strpos($tmpKey, '.') !== false) {
		// 	preg_match('/^(.+)(?:\.([^\.]+))$/', $tmpKey, $match);
		// 	$subKey = $match[2];
		// 	$tmpKey = $match[1];
		// 	if (array_key_exists($tmpKey, $this->config))
		// }
		return $this;
	}

	/**
	 * Add/merge in new config.
	 *
	 * @param Array $config An array of config.
	 * @param string $prefix The current dot notation prefix. For use when setting values.
	 * 
	 * @return $this Current instance for method chaining.
	 */
	public function addConfig($config, $prefix = '') {
		if (is_array($config)) {
			foreach ($config as $key => $val) {
				$longKey = $prefix . ($prefix != '' ? "." : '') . $key;
				if (isset($this->config[$longKey]) && is_array($this->config[$longKey]))
					$this->config[$longKey] = array_merge($this->config[$longKey], $val);
				else
					$this->config[$longKey] = $val;
				if (is_array($val) || is_object($val)) {
					$this->addConfig($val, $longKey);
				}
			}
		}
		return $this;
	}

	/**
	 * Set new config skipping config key generation.
	 *
	 * @param Array $config An array of config.
	 */
	public function setCompiledConfig($config) {
		$this->config = $config;
	}

	/**
	 * Add/merge new config skipping config key generation.
	 *
	 * @param Array $config An array of config.
	 */
	public function mergeCompiledConfig($config) {
		$this->config = \lynk\deepMerge($this->config, $config);
	}

	/**
	 * Load config from file.
	 *
	 * @param string Config file path.
	 * 
	 * @return $this Current instance for method chaining.
	 */
	public function loadConfig($file) {
		$this->addConfig($this->configService->get($file));
		return $this;
	}

	/**
	 * Get config data.
	 *
	 * @return Array The configuration.
	 */
	public function getData() {
		return $this->config;
	}

	/**
	 * Get config keys.
	 *
	 * @return Array Config keys.
	 */
	public function getKeys() {
		return array_keys($this->config);
	}

	/**
	 * Check whether config key exists.
	 *
	 * @param string $key The config key.
	 * 
	 * @return bool true if key exists, false otherwise.
	 */
	public function has($key) {
		return array_key_exists($key, $this->config);
	}

	/**
	 * Remove config by key.
	 *
	 * @param string $key The config key.
	 */
	public function remove($key) {
		unset($this->config[$key]);
	}
}