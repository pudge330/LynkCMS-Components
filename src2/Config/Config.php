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
 * @subpackage Config
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Config;

use Exception;
use Lynk\Component\Config\Converter;
use Symfony\Component\Yaml\Yaml;

/**
 * Config class for reading and writing various config file formats.
 *
 * @todo Implement use of flock when writing files.
 */
class Config {

	const FORMAT_INI = 'ini';
	const FORMAT_JSON = 'json';
	const FORMAT_PHP = 'php';
	const FORMAT_RAW = 'raw';
	const FORMAT_SERIALIZED = 'serialized';
	const FORMAT_YAML = 'yaml';

	/**
	 * @var Array An array of tokens or key-value pairs for interpolating into the config.
	 */
	protected $contextTokens;

	/**
	 * @var Closure A closure that can interpolate or modify the config values.
	 */
	protected $contextCallbacks;

	/**
	 * @var Array Context token wrap characters, [left, right] side.
	 */
	protected $contextWrap;

	/**
	 * @var array List of data converter classes.
	 */
	protected $converters;

	/**
	 * @param Array $contextTokens An array of tokens or key-value pairs for interpolating into the config.
	 * @param Array Context token wrap characters, [left, right] side.
	 */
	public function __construct($contextTokens = Array(), $contextWrap = Array()) {
		$this->contextTokens = [];
		$this->converters = [
			self::FORMAT_INI => new Converter\IniConverter(),
			self::FORMAT_JSON => new Converter\JsonConverter(),
			self::FORMAT_PHP => new Converter\PhpConverter(),
			self::FORMAT_RAW => new Converter\RawConverter(),
			self::FORMAT_SERIALIZED => new Converter\SerializedConverter(),
			self::FORMAT_YAML => new Converter\YamlConverter()
		];
		$this->contextWrap = sizeof($contextWrap) == 2 ? $contextWrap : Array('%', '%');
		$self = $this;
		$this->contextCallbacks = [
			'config_token_cb' => function($config) use ($self) {
				if (sizeof($self->contextTokens)) {
					$regex = '';
					array_walk($self->contextTokens, function($value, $key) use (&$regex) {
						$regex .= ($regex != '' ? '|' : '') . preg_quote($key);
					});
					$regex = "/" . preg_quote($self->contextWrap[0]) . "(" . $regex . ")" . preg_quote($self->contextWrap[1]) . "/";
					if (preg_match_all($regex, $config, $matches)) {
						foreach ($matches[1] as $match) {
							$config = str_replace("{$self->contextWrap[0]}{$match}{$self->contextWrap[1]}", $self->contextTokens[$match], $config);
						}
					}
				}
				return $config;
			}
		];
		$this->setToken($contextTokens);
	}



	/**
	 * Read and parse a config into a PHP array.
	 *
	 * @param string The config file path.
	 * @param Array Context tokens.
	 * 
	 * @return Array The config as a PHP array.
	 */
	public function get($path, $context = Array()) {
		$path = $this->interpolate($path);
		$pathParts = pathinfo($path);

		if (!isset($pathParts['extension']))
			$pathParts['extension'] = '';

		$type = $this->determineType($pathParts['extension']);

		if (!file_exists($path)) {
			throw new Exception(sprintf("Config::get() file %s does not exists in %s.", $pathParts['basename'], $pathParts['dirname']));
		}
		else if (!is_readable($path)) {
			throw new Exception(sprintf("Config::get() file %s in %s is not readable.", $pathParts['basename'], $pathParts['dirname']));
		}

		if (array_key_exists($type, $this->converters)) {
			$data = $this->converters[$type]->read($path);
			return $this->parse($type, $data, $context);
		}
		else {
			throw new Exception(sprintf("Config::get() file type '%s' has no registered converter for file '%s'.", $type, $path));
		}
	}

	/**
	 * Dump an array into a config file.
	 *
	 * @param string $path The config file path.
	 * @param mixed $data Optional. The data to write.
	 */
	public function set($path, $data) {
		$path = $this->interpolate($path);
		$pathParts = pathinfo($path);

		if (!isset($pathParts['extension']))
			$pathParts['extension'] = '';
		$pathParts['extension'] = strtolower($pathParts['extension']);

		$type = $this->determineType($pathParts['extension']);

		//--attempt to create directory if non-existent
		if (!is_dir($pathParts['dirname']))
			mkdir($pathParts['dirname'], 0777, true);
		if (!is_writable($pathParts['dirname'])) {
			throw new Exception(sprintf("Config::get() file %s in %s is not writable.", $pathParts['basename'], $pathParts['dirname']));
		}

		if (array_key_exists($type, $this->converters)) {
			return file_put_contents($path, $this->dump($type, $data));
		}
		else {
			throw new Exception(sprintf("Config::set() file type '%s' has no registered converter.", $type));
		}
	}

	/**
	 * Parses a config from a string.
	 *
	 * @param string $type One of the built in file types.
	 * @param string $data The data from the file.
	 * @param Array $context Optional. Context tokens.
	 * 
	 * @return mixed Depends on the data saved and format.
	 */
	public function parse($type, $data, $context = Array()) {
		if (array_key_exists($type, $this->converters)) {
			$data = $this->converters[$type]->parse($data);
			$data = $this->interpolate($data, $context);
			return $data;
		}
		else {
			throw new Exception(sprintf("Config::parse() data type '%s' has no registered converter'.", $type));
		}
	}

	/**
	 * Dump config into a writable string.
	 *
	 * @param $type The desired type or format of the data.
	 * @param mixed $data The data to write.
	 * 
	 * @return string The data as a writeable string.
	 */
	public function dump($type, $data) {
		if (array_key_exists($type, $this->converters)) {
			return $this->converters[$type]->dump($data);
		}
		else {
			throw new Exception(sprintf("Config::dump() data type '%s' has no registered converter'.", $type));
		}
	}

	public function determineType($extension) {
		if (trim($extension) === '')
			return self::FORMAT_RAW;
		foreach ($this->converters as $type => $converter) {
			if (in_array($extension, $converter->extensions())) {
				return $type;
			}
		}
	}

	/**
	 * Interpolate context into config.
	 *
	 * @param string $config The config.
	 * @param Array $context Context tokens.
	 * 
	 * @return string The interpolated string.
	 */
	public function interpolate($config, $context = []) {
		$contextCallbacks = $this->contextCallbacks;
		$tmpContextCallbacks = [];
		if (sizeof($context)) {
			$t = $this;
			$tmpContextCallbacks['__TMP__CONTEXT__CALLBACK__' . rand()] = function($config) use ($t, $context) {
				$regex = '';
				array_walk($context, function($value, $key) use (&$regex) {
					$regex .= ($regex != '' ? '|' : '') . preg_quote($key, '/');
				});
				$regex = "/" . preg_quote($t->contextWrap[0], '/') . "(" . $regex . ")" . preg_quote($t->contextWrap[1], '/') . "/";
				if (preg_match_all($regex, $config, $matches)) {
					foreach ($matches[1] as $match) {
						$config = str_replace("{$this->contextWrap[0]}{$match}{$this->contextWrap[1]}", $context[$match], $config);
					}
				}
				return $config;
			};
		}
		$contextCallbacks = array_merge($tmpContextCallbacks, $contextCallbacks);
		$interpolateFuncs = function($value) use ($contextCallbacks) {
			foreach ($contextCallbacks as $f) {
				$value = $f($value);
			}
			return $value;
		};
		if ($config) {
			if (is_array($config) || is_object($config)) {
				foreach ($config as $key => &$value) {
					if (is_array($value) || is_object($value)) {
						$value = $this->interpolate($value, $context);
					}
					else {
						if (is_string($value)) {
							$value = $interpolateFuncs($value);
						}
					}
				}
			}
			else {
				if (is_string($config)) {
					$config = $interpolateFuncs($config);
				}
			}
		}
		return $config;
	}

	/**
	 * Returns the context tokens.
	 *
	 * @return Array An array of context tokens.
	 */
	public function getTokens() {
		return $this->contextTokens;
	}

	/**
	 * Get specific token.
	 *
	 * @param string $key The context key.
	 * 
	 * @return mixed Context value.
	 */
	public function getToken($key) {
		if (is_array($key)) {
			$result = Array();
			foreach ($key as $k) {
				$result[$k] = $this->getToken($k);
			}
			return $result;
		}
		else {
			if ($this->hasToken($key)) {
				return $this->contextTokens[$key];
			}
		}
	}

	/**
	 * Set token(s).
	 *
	 * @param Array|stirng The key or array of tokens.
	 * @param mixed The context value.
	 */
	public function setToken() {
		$args = func_get_args();
		$argSize = sizeof($args);
		switch ($argSize) {
			case 1:
				if (is_array($args[0])) {
					foreach ($args[0] as $tokenKey => $tokenValue) {
						$this->setToken($tokenKey, $tokenValue);
					}
				}
				else {
					throw new Exception(sprintf("Config::setToken() missing second argument.", $argSize));
				}
			break;
			case 2:
				$this->contextTokens[$args[0]] = $args[1];
			break;
			default:
				throw new Exception(sprintf("Config::setToken() requires either 1 or 2 arguments, %d passed.", $argSize));
			break;
		}
	}

	/**
	 * Checks if token exists.
	 *
	 * @param string $key The context key.
	 * 
	 * @return bool True if it exists, false otherwise.
	 */
	public function hasToken($key) {
		if (is_array($key)) {
			$result = Array();
			foreach ($key as $k) {
				$result[$k] = $this->hasToken($k);
			}
			return $result;
		}
		else {
			return array_key_exists($key, $this->contextTokens);
		}
	}

	/**
	 * Remove context token.
	 *
	 * @param string $key The context token key.
	 */
	public function removeToken($key) {
		if (is_array($key)) {
			foreach ($key as $k) {
				$this->removeToken($k);
			}
		}
		else {
			unset($this->contextTokens[$key]);
		}
	}

	/**
	 * Get context callbacks.
	 *
	 * @return Array An array of context callbacks.
	 */
	public function getCallbacks() {
		return $this->contextCallbacks;
	}

	/**
	 * Get context callback by key.
	 *
	 * @param string|Array $key The context callback key.
	 * 
	 * @return Closure The context closure.
	 */
	public function getCallback($key) {
		if (is_array($key)) {
			$result = Array();
			foreach ($key as $k) {
				$result[$k] = $this->getCallback($k);
			}
			return $result;
		}
		else {
			if ($this->hasCallback($key)) {
				return $this->contextCallbacks[$key];
			}
		}
	}

	/**
	 * Set context callback.
	 *
	 * @param Array|stirng The key or array of context closures.
	 * @param mixed The context closure.
	 */
	public function setCallback() {
		$args = func_get_args();
		$argSize = sizeof($args);
		switch ($argSize) {
			case 1:
				if (is_array($args[0])) {
					foreach ($args[0] as $callbackKey => $callbackValue) {
						$this->setCallback($callbackKey, $callbackValue);
					}
				}
				else {
					throw new Exception(sprintf("Config::setCallback() missing second argument.", $argSize));
				}
			break;
			case 2:
				$this->contextCallbacks[$args[0]] = $args[1];
			break;
			default:
				throw new Exception(sprintf("Config::setCallback() requires either 1 or 2 arguments, %d passed.", $argSize));
			break;
		}
	}

	/**
	 * Checks if context closure exists.
	 *
	 * @param string $key The context key.
	 * 
	 * @return bool True if it exists, false otherwise.
	 */
	public function hasCallback($key) {
		if (is_array($key)) {
			$result = Array();
			foreach ($key as $k) {
				$result[$k] = $this->hasCallback($k);
			}
			return $result;
		}
		else {
			return array_key_exists($key, $this->contextCallbacks);
		}
	}

	/**
	 * Remove context closure.
	 *
	 * @param string $key The context token key.
	 */
	public function removeCallback($key) {
		if (is_array($key)) {
			foreach ($key as $k) {
				$this->removeCallback($k);
			}
		}
		else {
			unset($this->contextCallbacks[$key]);
		}
	}

	/**
	 * Get a data converter instance.
	 * 
	 * @param string $type The converter type
	 * 
	 * @return ConverterInsance The converter object
	 * 
	 * @throws Exception
	 */
	public function getConverter($type) {
		if (array_key_exists($type, $this->converters)) {
			return $this->converters[$type];
		}
		throw new Exception("Config::getConverter() Error: no converter type '{$type}' exists.");
	}

	/**
	 * Set a data converter instance.
	 * 
	 * @param string $type The converter type
	 * @param Converter\ConverterInstance The converter instance
	 */
	public function setConverter($type, Converter\ConverterInterface $converter) {
		$this->converters[$type] = $converter;
	}

	/**
	 * Static method to read and parse a config into a PHP array.
	 *
	 * @param string $path The config file path.
	 * @param Array $context Optional. Context tokens.
	 * @param Array $contextWrap Context token wrap characters, [left, right] side.
	 * 
	 * @return Array The config as a PHP array.
	 */
	public static function getConfig($path, $context = Array(), $contextWrap = Array()) {
		$c = new static($context, $contextWrap);
		return $c->get($path);
	}

	/**
	 * Static method to dump an array into a config file.
	 *
	 * @param string $path The config file path.
	 * @param mixed $data The data to write.
	 */
	public static function setConfig($path, $data) {
		$c = new static();
		$c->set($path, $data);
	}

	/**
	 * Static method to parses a config from a string.
	 *
	 * @param string $type One of the built in file types.
	 * @param string $data The data from the file.
	 * @param Array $context Optional. Context tokens.
	 * @param Array $contextWrap Context token wrap characters, [left, right] side.
	 * 
	 * @return mixed Depends on the data saved and format.
	 */
	public static function parseConfig($type, $data, $context = Array(), $contextWrap = Array()) {
		$c = new static($context, $contextWrap);
		return $c->parse($type, $data);
	}

	/**
	 * Static method to dump config into a writable string.
	 *
	 * @param $type The desired type or format of the data.
	 * @param mixed $data The data to write.
	 * 
	 * @return string The data as a writeable string.
	 */
	public static function dumpConfig($type, $data) {
		$c = new static();
		return $c->dump($type, $data);
	}

	/**
	 * Static method to return a converter class.
	 *
	 * @param string $name The converter name.
	 */
	public static function configConverter($name) {
		$c = new static();
		return $c->getConverter($name);
	}
}