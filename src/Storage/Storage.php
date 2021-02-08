<?php
namespace BGStudios\Component\Storage;

use Exception;
use PDO;
use BGStudios\Component\Connection;
use Symfony\Component\Yaml\Yaml;

class Storage implements StorageInterface {
	public static $isSymfonyYamlSupported = null;
	protected $storage;
	protected $storageType;
	protected $options;
	/*
		$dirOrPdo - takes the 'cache/tmp' directory or a PDO/ConnectionWrapped object
	*/
	public function __construct($dirOrPdo, $opts = Array()) {
		if (static::$isSymfonyYamlSupported === null) {
			static::$isSymfonyYamlSupported = class_exists('\Symfony\Component\Yaml\Yaml');
		}
		$opts = array_merge(Array(
			'table' => 'database_storage'
			,'columns' => Array('identifier', 'value')
			,'namespace' => null
			,'hashName' => false
			,'hashAlgo' => 'sha1'
			,'encodeData' => false
			,'encryptData' => false
			,'encryptKey' => null
			,'storageType' => 'serialize' //--serialize|json|yaml
			,'returnJsonArray' => true
		), $opts);
		if (is_string($dirOrPdo)) {
			$dirOrPdo = rtrim($dirOrPdo, DIRECTORY_SEPARATOR);
			if (!file_exists("{$dirOrPdo}/{$opts['namespace']}")) {
				if (!mkdir("{$dirOrPdo}/{$opts['namespace']}", 0755, true)) {
					throw new Exception("Cannot create directory {$dirOrPdo}");
				}
			}
			if (!is_writable("{$dirOrPdo}/{$opts['namespace']}")) {
				throw new Exception("Directory {$dirOrPdo} is not writable");
			}
			$this->storage = new FileSystemStorage("{$dirOrPdo}/{$opts['namespace']}");
			$this->storageType = 'filesystem';
		}
		else if ($dirOrPdo instanceof PDO || $dirOrPdo instanceof Connection\Connection || $dirOrPdo instanceof Connection\ConnectionWrapped) {
			$this->storage = new DatabaseStorage($dirOrPdo, Array(
				'table' => $opts['table'],
				'columns' => $opts['columns']
			));
			$this->storageType = 'database';
		}
		else {
			throw new Exception('BGStudios\\Component\\Storage\\Storage requires the first parameter to be either a directory, PDO object or ConnectionWrapped object');
		}
		if ($opts['encryptData'] && !$opts['encryptKey']) {
			throw new Exception('Must provide an encryption key if using the \'encryptData\' option');
		}
		$this->options = $opts;
	}
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
	public function has($key) {
		return $this->storage->has($this->createKey($key));
	}
	public function remove($key) {
		return $this->storage->remove($this->createKey($key));
	}
	public function createKey($key) {
		$key = $this->storageType == 'database' && $this->options['namespace']
			? "{$this->options['namespace']}.{$key}" : $key;
		$key = $this->options['hashName'] ? hash($this->options['hashAlgo'], $key) : $key;
		return $key;
	}
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
			$data = \bgs\encrypt($data, $this->options['encryptKey']);
			$flags .= 'e';
		}
		return ($flags != '' ? "{$flags}\\" : '') . $data;
	}
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
			$data = \bgs\decrypt($data, $this->options['encryptKey']);
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