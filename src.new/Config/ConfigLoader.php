<?php
/**
 * This file is part of the LynkCMS Config Component.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS PHP Components
 * @subpackage Config
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Config;

use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Config loader class, load config from an array of files into one config array,
 * allows importing addition files from existing ones. 
 */
class ConfigLoader {

	/**
	 * @var string Root directory for file loading.
	 */
	protected $root;

	/**
	 * @var Closure Import callback function.
	 */
	protected $importCallback;

	/**
	 * @var Config Instance of the LynkCMS config class.
	 */
	protected $config;

	/**
	 * @param string $root Root directory for file loading.
	 * @param Config $config Optional. Config Instance of the LynkCMS config class.
	 * @param Closure $importCallback Optional. Import callback function.
	 */
	public function __construct($root, $config = null, $importCallback = Array()) {
		$this->root = rtrim($root, '/');
		$this->importCallback = $importCallback ?: function() {};
		$this->config = $config instanceof Config ? $config : new Config();
	}

	/**
	 * Load an array of config files.
	 *
	 * @param string|Array $files A config file or array of config files.
	 * @param Closure $importCallback Optional. Import callback function.
	 * @return Array The config.
	 */
	public function load($files, $importCallback = null) {
		if (!is_array($files))
			$files = Array($files);
		$files = $this->processPaths($files);
		$config = [];
		foreach ($files as $file) {
			$fileBasename = basename($file);
			$fileDirname = dirname($file);
			$config = $this->deepMerge($config, $this->importResourceFromFile($fileBasename, $fileDirname, $fileDirname, $importCallback));
		}
		return $config;
	}

	/**
	 * Load a config file.
	 *
	 * @param string $file A config file path.
	 * @param string $origin The original file directroy entry point.
	 * @param string $dirname The current file directroy entry point.
	 * @param Closure $importCallback Optional. Import callback function.
	 * @param int $level Optional. Curernt depth level? Not sure if used or for what.
	 * @return Array The config.
	 */
	protected function importResourceFromFile($file, $origin, $dirname, $importCallback = null, $level = 0) {
		if (!$importCallback) {
			$importCallback = function() {};
		}
		$loaded = Array();
		$file = $this->resolvePath($file, $dirname, $origin);
		if ($file && file_exists($file)) {
			$loadedDirname = dirname($file);
			$loaded = $this->config->get($file);
			if (!is_array($loaded)) {
				return $loaded;
			}
			$loadedImport = Array();
			if (is_array($loaded) && isset($loaded['import'])) {
				$loadedImport = $loaded['import'];
				unset($loaded['import']);
			}
			foreach ($loadedImport as $loadedImportValue) {
				$loadedImportResource = isset($loadedImportValue['resource']) && $loadedImportValue['resource']
					? $loadedImportValue['resource'] 
					: null
				;
				$loadedImportParent = isset($loadedImportValue['parent']) && $loadedImportValue['parent']
					? $loadedImportValue['parent'] 
					: null
				;
				$returned = $this->importResourceFromFile($loadedImportResource, $origin, $loadedDirname, $importCallback, $level);
				$classImportCallback = $this->importCallback;
				$classImportCallback($loadedImportParent ?: '__BASE__', $returned, $loadedImportValue, $level);
				$importCallback($loadedImportParent ?: '__BASE__', $returned, $loadedImportValue, $level);
				if ($loadedImportParent) {
					if (!isset($loaded[$loadedImportParent])) {
						$loaded[$loadedImportParent] = Array();
					}
					$loaded[$loadedImportParent] = is_array($returned)
						? $this->deepMerge($loaded[$loadedImportParent], $returned)
						: $returned;
				}
				else {
					$loaded = is_array($returned) ? $this->deepMerge($loaded, $returned) : $returned;
				}
			}
			foreach ($loaded as $loadedKey => $loadedValue) {
				$loadedValueImport = Array();
				if (is_array($loadedValue) && isset($loadedValue['import'])) {
					$loadedValueImport = $loadedValue['import'];
					unset($loadedValue['import']);
				}
				foreach ($loadedValueImport as $loadedValueImportValue) {
					$loadedValueImportValueResource = isset($loadedValueImportValue['resource']) && $loadedValueImportValue['resource']
						? $loadedValueImportValue['resource'] 
						: null
					;
					$loadedValueImportValueParent = isset($loadedValueImportValue['parent']) && $loadedValueImportValue['parent']
						? $loadedValueImportValue['parent'] 
						: null
					;
					$returned = $this->importResourceFromFile($loadedValueImportValueResource, $origin, $loadedDirname, $importCallback, $level);
					$classImportCallback = $this->importCallback;
					$classImportCallback($loadedValueImportValueParent ?: $loadedKey, $returned, $loadedValueImportValue, $level);
					$importCallback($loadedValueImportValueParent ?: $loadedKey, $returned, $loadedValueImportValue, $level);
					if ($loadedValueImportValueParent) {
						if (!isset($loadedValue[$loadedValueImportValueParent])) {
							$loadedValue[$loadedValueImportValueParent] = Array();
						}
						$loadedValue[$loadedValueImportValueParent] = is_array($returned)
							? $this->deepMerge($loadedValue[$loadedValueImportValueParent], $returned)
							: $returned;
					}
					else {
						$loadedValue = is_array($returned)
							? $this->deepMerge($loadedValue, $returned)
							: $returned;
					}
				}
				$loaded[$loadedKey] = $loadedValue;
			}
		}
		return $loaded;
	}

	/**
	 * Resolve dynamic directory path.
	 *
	 * @param string $path File path.
	 * @param string $dirname Current directory.
	 * @param string $orifin Directory origin.
	 * @return string The resolved directory path.
	 */
	protected function resolvePath($path, $dirname, $origin) {
		if (preg_match('/^@root/', $path)) {
			$path = str_replace('@root', $this->root, $path);
		}
		else if ($origin && preg_match('/^@origin/', $path)) {
			$path = str_replace('@origin', $origin, $path);
		}
		else {
			$path = $this->config->interpolate($path);
			if (!preg_match('/^\//', $path)) {
				$path = "{$dirname}/{$path}";
			}
		}
		return $path;
	}

	/**
	 * Find correct path from an array of paths. Used for multiple source types.
	 *
	 * @param $files An array of files/sub-array of files.
	 * @return Array Final list of files to import.
	 */
	protected function processPaths($files) {
		$final = Array();
		foreach ($files as $fileKey => $file) {
			$tmp = $this->firstExistingFile(is_array($file) ? $file : [$file]);
			if ($tmp)
				$final[] = $tmp;
		}
		return $final;
	}

	/**
	 * Find first existing file in list of files.
	 *
	 * @param Array $array List of files.
	 * @return string The first existing file.
	 */
	protected function firstExistingFile($array) {
		foreach ($array as $item) {
			$item = preg_match('/^\//', $item) ? $item : "{$this->root}/{$item}";
			if (file_exists($item))
				return $item;
		}
	}

	/**
	 * Deep-merge array.
	 *
	 * @param Array The array(s) to merge. Each array is an individual argument.
	 * @return Array The merged array.
	 */
	protected function deepMerge() {
		$args = func_get_args();
		if (sizeof($args) == 0) {
			return [];
		}
		$merged = array_shift($args);
		foreach ($args as $arg) {
			foreach ($arg as $key => &$value) {
				if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
					$merged[$key] = $this->deepMerge($merged[$key], $value);
				}
				else {
					$merged[$key] = $value;
				}
			}
		}
		return $merged;
	}
}