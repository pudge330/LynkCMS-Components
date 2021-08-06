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
 * @subpackage Storage
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Storage;

use Exception;
use PDO;
use LynkCMS\Component\Connection;
use Symfony\Component\Yaml\Yaml;

/**
 * Database or file system key value based storage with ability to encode or encrypt data.
 */
class Storage implements StorageInterface {

	/**
	 * @var bool Whether or not Symfony's YAML component is available.
	 */
	public static $isSymfonyYamlSupported = null;

	/**
	 * @var DatabaseStorage|FileSystemStorage Storage object.
	 */
	protected $storage;

	/**
	 * @var string Storage format. JSON, yaml or serialized.
	 */
	protected $storageType;

	/**
	 * @var Array Storage options.
	 */
	protected $options;

	/**
	 * @param string|PDO|Connection|ConnectionWrapped $dirOrPdo Directory location or PDO object.
	 * @param Array $opts Options for storage.
	 * 
	 * @throws \Exception
	 */
	public function __construct(StorageInterface $storage, $opts = Array()) {
		if (static::$isSymfonyYamlSupported === null) {
			static::$isSymfonyYamlSupported = class_exists('\Symfony\Component\Yaml\Yaml');
		}
		$opts = array_merge(Array(
			'namespace' => null
			,'hashName' => false
			,'hashAlgo' => 'sha1'
			,'encodeData' => false
			,'encryptData' => false
			,'encryptKey' => null
			,'storageType' => 'serialize' //--serialize|json|yaml
			,'returnJsonArray' => true
		), $opts);

		$this->storage = $storage;

		if ($opts['namespace'] && !preg_match('/^[a-zA-Z0-9-_+\ ]+$/', $opts['namespace'])) {
			throw new Exception("Storage: 'namespace' must match 'a-zA-Z0-9-_+ '");
		}

		if ($opts['encryptData'] && !$opts['encryptKey']) {
			throw new Exception("Storage: Must provide an encryption key if using the 'encryptData' option");
		}
		$this->options = $opts;
	}

	/**
     * Get value(s).
     * 
     * @param mixed $key Either a string or array of string keys.
     * 
     * @return mixed Value or array of values.
     */
	public function get($key) {
		if ($key == null) {
			return null;
		}
		$singleResult = false;
		if (!is_array($key)) {
			$singleResult = true;
			$key = Array($key);
		}
		foreach ($key as $i => $k) {
			$key[$i] = $this->createKey($k);
		}
		$result = $this->storage->get($key);
		foreach ($result as $k => $v) {
			$result[$k] = $this->decodeData($v);
		}
		if ($singleResult) {
			$resultValues = array_values($result);
			return $resultValues[0];
		}
		return $result;
	}

	/**
     * Set value(s).
     * 
     * @param mixed $key A key as a string or an array of key value pairs.
     * @param mixed $value The value to store or null if array is passsed for $key.
     * 
     * @return bool True if successful or false if not.
     */
	public function set($key, $value = null) {
		if (!is_array($key)) {
			$key = Array($key => $value);
		}
		$final = Array();
		foreach ($key as $k => $v) {
			$final[$this->createKey($k)] = $this->encodeData($v);
		}
		return $this->storage->set($final);
	}

	/**
     * Check whether or not a key exists.
     * 
     * @param string $key Key to check for.
     * 
     * @return bool True if key exists, false if not.
     */
	public function has($key) {
		return $this->storage->has($this->createKey($key));
	}

	/**
     * Remove a value from storage.
     * 
     * @param string $key The key of the value to be removed.
     * 
     * @return bool True if successfully removed the value, false if not.
     */
	public function remove($key) {
		return $this->storage->remove($this->createKey($key));
	}

	/**
	 * Create value key with namespace if required and/or hash key.
	 *
	 * @param string $key Key name.
	 */
	public function createKey($key) {
		$key = $this->options['namespace']
			? "{$this->options['namespace']}.{$key}" : $key;
		$key = $this->options['hashName'] ? hash($this->options['hashAlgo'], $key) : $key;
		return $key;
	}

	/**
	 * Encode data.
	 *
	 * @param mixed $data Data to be encoded.
	 * 
	 * @return string Encoded data.
	 */
	public function encodeData($data) {
		$flags = '';
		if ($this->options['storageType'] == 'json') {
			$data = json_encode($data);
			$flags .= 'j';
		}
		else if ($this->options['storageType'] == 'yaml' && static::$isSymfonyYamlSupported) {
			$data = Yaml::dump($data);
			$flags .= 'y';
		}
		else if ($this->options['storageType'] == 'serialize') {
			$data = serialize($data);
			$flags .= 's';
		}
		if ($this->options['encodeData']) {
			$data = base64_encode($data);
			$flags .= 'b';
		}
		else if ($this->options['encryptData']) {
			$data = \lynk\encrypt($data, $this->options['encryptKey']);
			$flags .= 'e';
		}
		return ($flags != '' ? "{$flags}\\" : '') . $data;
	}

	/**
	 * Decode data.
	 *
	 * @param mixed $data Data to be decoded.
	 * 
	 * @return mixed decoded data.
	 */
	public function decodeData($data) {
		$flags = '';
		if (preg_match('/^([bejsy]+)\\\/', $data, $match)) {
			$flags = $match[1];
			$data = substr($data, strlen($match[0]));
		}
		if (strpos($flags, 'b') !== false) {
			$data = base64_decode($data);
		}
		else if (strpos($flags, 'e') !== false) {
			$data = \lynk\decrypt($data, $this->options['encryptKey']);
		}
		if (strpos($flags, 'j') !== false) {
			$data = json_decode($data, $this->options['returnJsonArray']);
		}
		else if (strpos($flags, 'y') !== false) {
			$data = Yaml::parse($data);
		}
		else if (strpos($flags, 's') !== false) {
			$data = unserialize($data);
		}
		return $data;
	}
}