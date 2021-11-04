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

use Exception;
use Symfony\Component\Yaml\Yaml;
use LynkCMS\Component\Config\Formatter;

/**
 * Config class for reading and writing various config file formats.
 *
 * @todo Implement use of flock when writing files.
 * @todo Make AbstractFormatter class, modify current formatters to inherit and implement 'parse' and 'dump' methods.
 * @todo Implement Formatters for each config type to make this library more modular
 *       and add ability to load additional formatters.
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
	 * @var Formatter\PhpFormatter Class for formatting php arrays into a writable string.
	 */
	protected $phpFormatter;

	/**
	 * @var Formatter\PhpFormatter Class for formatting php arrays into a valid INI writable string.
	 */
	protected $iniFormatter;

	/**
	 * @var Array Context token wrap characters, [left, right] side.
	 */
	protected $contextWrap;

	/**
	 * @param Array $contextTokens An array of tokens or key-value pairs for interpolating into the config.
	 * @param Array Context token wrap characters, [left, right] side.
	 */
	public function __construct($contextTokens = Array(), $contextWrap = Array()) {
		$this->contextTokens = [];
		$this->phpFormatter = new Formatter\PhpFormatter();
		$this->iniFormatter = new Formatter\IniFormatter();
		$this->contextWrap = sizeof($contextWrap) == 2 ? $contextWrap : Array('%', '%');
		$t = $this;
		$this->contextCallbacks = [
			'config_token_cb' => function($config) use ($t) {
				if (sizeof($t->contextTokens)) {
					$regex = '';
					array_walk($t->contextTokens, function($value, $key) use (&$regex) {
						$regex .= ($regex != '' ? '|' : '') . preg_quote($key);
					});
					$regex = "/" . preg_quote($t->contextWrap[0]) . "(" . $regex . ")" . preg_quote($t->contextWrap[1]) . "/";
					if (preg_match_all($regex, $config, $matches)) {
						foreach ($matches[1] as $match) {
							$config = str_replace("{$this->contextWrap[0]}{$match}{$this->contextWrap[1]}", $t->contextTokens[$match], $config);
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
	 * @param Array $options Optional. An array of options depending on config format.
	 * 
	 * @return Array The config as a PHP array.
	 */
	public function get($path, $context = Array(), $options = Array()) {
		$path = $this->interpolate($path);
		$pathParts = pathinfo($path);
		if (!isset($pathParts['extension']))
			$pathParts['extension'] = '';
		if ($pathParts['extension'] == 'json') { $type = Config::FORMAT_JSON; }
		else if ($pathParts['extension'] == 'serialized') { $type = Config::FORMAT_SERIALIZED; }
		else if ($pathParts['extension'] == 'php') { $type = Config::FORMAT_PHP; }
		else if ($pathParts['extension'] == 'yml' || $pathParts['extension'] == 'yaml') { $type = Config::FORMAT_YAML; }
		else if ($pathParts['extension'] == 'ini') { $type = Config::FORMAT_INI; }
		else { $type = Config::FORMAT_RAW; }
		if (!file_exists($path)) {
			throw new Exception(sprintf("Config::get() file %s does not exists in %s.", $pathParts['basename'], $pathParts['dirname']));
		}
		else if (!is_readable($path)) {
			throw new Exception(sprintf("Config::get() file %s in %s is not readable.", $pathParts['basename'], $pathParts['dirname']));
		}
		switch ($type) {
			case self::FORMAT_JSON:
				$data = file_get_contents($path);
				if (!$data || $data == '')
					$data = '{}';
				return $this->parse(self::FORMAT_JSON, $data, $context, $options);
			break;
			case self::FORMAT_PHP:
				$data = include $path;
				return $this->parse(self::FORMAT_PHP, $data, $context, $options);
			break;
			case self::FORMAT_YAML:
				$data = file_get_contents($path);
				if (!$data || $data == '')
					$data = '{}';
				return $this->parse(self::FORMAT_YAML, $data, $context, $options);
			break;
			case self::FORMAT_INI:
				$data = file_get_contents($path);
				return $this->parse(self::FORMAT_INI, $data, $context, $options);
			break;
			case self::FORMAT_SERIALIZED:
				$data = file_get_contents($path);
				if (!$this->isSerialized($data)) {
					throw new Exception(sprintf("Config::get() file %s in %s is is corrupted.", $pathParts['basename'], $pathParts['dirname']));
				}
				return $this->parse(self::FORMAT_SERIALIZED, $data, $context, $options);
			break;
			case self::FORMAT_RAW:
			default:
				$data = file_get_contents($path);
				return $this->parse(self::FORMAT_RAW, $data, $context, $options);
			break;
		}
	}

	/**
	 * Dump an array into a config file.
	 *
	 * @param string $path The config file path.
	 * @param mixed $data Optional. The data to write.
	 * @param Array $options Optional. An array of options depending on config format.
	 */
	public function set($path, $data, $options = Array()) {
		$path = $this->interpolate($path);
		$pathParts = pathinfo($path);
		if (!isset($pathParts['extension']))
			$pathParts['extension'] = '';
		$pathParts['extension'] = strtolower($pathParts['extension']);
		if ($pathParts['extension'] == 'json') { $type = Config::FORMAT_JSON; }
		else if ($pathParts['extension'] == 'serialized') { $type = Config::FORMAT_SERIALIZED; }
		else if ($pathParts['extension'] == 'php') { $type = Config::FORMAT_PHP; }
		else if ($pathParts['extension'] == 'yml' || $pathParts['extension'] == 'yaml') { $type = Config::FORMAT_YAML; }
		else if ($pathParts['extension'] == 'ini') { $type = Config::FORMAT_INI; }
		else { $type = Config::FORMAT_RAW; }
		if (!is_dir($pathParts['dirname']))
			mkdir($pathParts['dirname'], 0777, true);
		if (!is_writable($pathParts['dirname'])) {
			throw new Exception(sprintf("Config::get() file %s in %s is not writable.", $pathParts['basename'], $pathParts['dirname']));
		}
		switch ($type) {
			case self::FORMAT_JSON:
				return file_put_contents($path, $this->dump(self::FORMAT_JSON, $data, $options));
			break;
			case self::FORMAT_PHP:
				return file_put_contents($path, $this->dump(self::FORMAT_PHP, $data, $options));
			break;
			case self::FORMAT_YAML:
				return file_put_contents($path, $this->dump(self::FORMAT_YAML, $data, $options));
			break;
			case self::FORMAT_INI:
				return file_put_contents($path, $this->dump(self::FORMAT_INI, $data, $options));
			break;
			case self::FORMAT_SERIALIZED:
				return file_put_contents($path, $this->dump(self::FORMAT_OBJECT, $data, $options));
			break;
			case self::FORMAT_RAW:
			default:
				return file_put_contents($path, $this->dump(self::FORMAT_FILE, $data, $options));
			break;
		}
	}

	/**
	 * Parses a config from a string.
	 *
	 * @param string $type One of the built in file types.
	 * @param string $data The data from the file.
	 * @param Array $context Optional. Context tokens.
	 * @param Array $options Optional. An array of options depending on config format.
	 * 
	 * @return mixed Depends on the data saved and format.
	 */
	public function parse($type, $data, $context = Array(), $options = Array()) {
		switch ($type) {
			case self::FORMAT_JSON:
				$assoc = isset($options['assoc']) ? $options['assoc'] : true;
				$data = json_decode($data, $assoc);
				$data = $this->interpolate($data, $context);
			break;
			case self::FORMAT_PHP:
				$data = $this->interpolate($data, $context);
			break;
			case self::FORMAT_YAML:
				$data = Yaml::parse($data);
				$data = $this->interpolate($data, $context);
			break;
			case self::FORMAT_INI:
				$data = parse_ini_string($data, true, INI_SCANNER_TYPED);
				$data = $this->interpolate($data, $context);
			break;
			case self::FORMAT_SERIALIZED:
				$data = unserialize($data);
				$data =  $this->interpolate($data, $context);
			break;
			case self::FORMAT_RAW:
			default:
				if (\lynk\startsWith($data, '[serialized]')) {
					$data = unserialize(substr($data, 12));
					$data = $this->interpolate($data, $context);
				}
			break;
		}
		return $data;
	}

	/**
	 * Dump config into a writable string.
	 *
	 * @param $type The desired type or format of the data.
	 * @param mixed $data The data to write.
	 * @param Array $options Optional. An array of options depending on config format.
	 * 
	 * @return string The data as a writeable string.
	 */
	public function dump($type, $data, $options = Array()) {
		switch ($type) {
			case self::FORMAT_JSON:
				$data = json_encode($data, JSON_PRETTY_PRINT);
			break;
			case self::FORMAT_PHP:
				$data = "<?php\nreturn " . $this->phpFormatter->formatArray($data) . ";";
			break;
			case self::FORMAT_YAML:
				$data = Yaml::dump($data, 200);
			break;
			case self::FORMAT_INI:
				$data = $this->iniFormatter->formatArray($data);
			break;
			case self::FORMAT_SERIALIZED:
				$data = serialize($data);
			break;
			case self::FORMAT_RAW:
			default:
				if (gettype($data) != 'string')
					$data = "[serialized]" . serialize($data);
				break;
			break;
		}
		return $data;
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
	 * Return formatter class.
	 *
	 * @param string $name The formatter name.
	 */
	public function formatter($name) {
		if (isset($this->{"{$name}Formatter"}))
			return $this->{"{$name}Formatter"};
		else
			throw New Exception("Config::formatter() Error: formatter named '{$name}' does not exist.");
	}

	/**
	 * Determine if string is a serialized.
	 *
	 * @param string $string String to check.
	 * 
	 * @return bool True if serialized, false otherwise.
	 */
	protected function isSerialized($string) {
		return ($string == serialize(false) || @unserialize($string) !== false);
	}

	/**
	 * Determines if string starts with a specific value.
	 *
	 * @param string $haystack The string to check for the other string, the haystack.
	 * @param string $needle The string to check for.
	 * 
	 * @return bool True if the haystack string starts with the needle string.
	 */
	protected function startsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) !== FALSE && strpos($haystack, $needle) === 0;
	}

	/**
	 * Determine if array is an associative array, mixed arrays included.
	 *
	 * @param Array $a The array to test.
	 * 
	 * @return bool true if array is assoc/mixed or false otherwise.
	 */
	protected function isAssoc(array $a) {
		foreach (array_keys($a) as $k)
			if (!is_int($k)) return true;
		return false;
	}

	/**
	 * Static method to read and parse a config into a PHP array.
	 *
	 * @param string $path The config file path.
	 * @param Array $context Optional. Context tokens.
	 * @param Array $options Optional. An array of options depending on config format.
	 * 
	 * @return Array The config as a PHP array.
	 */
	public static function getConfig($path, $context = Array(), $options = Array()) {
		$cwrap = isset($options['contextWrap']) ? $options['contextWrap'] : Array();
		unset($options['contextWrap']);
		$c = new static($context, $cwrap);
		return $c->get($path, Array(), $options);
	}

	/**
	 * Static method to dump an array into a config file.
	 *
	 * @param string $path The config file path.
	 * @param mixed $data The data to write.
	 * @param Array $options Optional. An array of options depending on config format.
	 */
	public static function setConfig($path, $data, $options = Array()) {
		$c = new static();
		$c->set($path, $data, $options);
	}

	/**
	 * Static method to parses a config from a string.
	 *
	 * @param string $type One of the built in file types.
	 * @param string $data The data from the file.
	 * @param Array $context Optional. Context tokens.
	 * @param Array $options Optional. An array of options depending on config format.
	 * 
	 * @return mixed Depends on the data saved and format.
	 */
	public static function parseConfig($type, $data, $context = Array(), $options = Array()) {
		$cwrap = isset($options['contextWrap']) ? $options['contextWrap'] : Array();
		unset($options['contextWrap']);
		$c = new static($context, $cwrap);
		return $c->parse($type, $data, Array(), $options);
	}

	/**
	 * Static method to dump config into a writable string.
	 *
	 * @param $type The desired type or format of the data.
	 * @param mixed $data The data to write.
	 * @param Array $options Optional. An array of options depending on config format.
	 * 
	 * @return string The data as a writeable string.
	 */
	public static function dumpConfig($type, $data, $options = Array()) {
		$c = new static();
		return $c->dump($type, $data, $options);
	}

	/**
	 * Static method to return a formatter class.
	 *
	 * @param string $name The formatter name.
	 */
	public static function configFormatter($name) {
		$c = new static();
		return $c->formatter($name);
	}

	/**
	 * Static method to interpolate context into config.
	 *
	 * @param string $config The config.
	 * @param Array $context Optional. Context tokens.
	 * 
	 * @return string The interpolated string.
	 */
	public static function configInterpolate($config, $context = Array()) {
		$c = new static();
		return $c->interpolate($config, $context);
	}
}