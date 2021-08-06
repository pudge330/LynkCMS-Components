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
 * @subpackage Session
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Session;

use Datetime;
use DateInterval;
use LynkCMS\Component\Container\GlobalArrayContainer;
use Symfony\Component\Yaml\Yaml;

/**
 * Session storage with caching.
 */
class SessionCache {

	/**
	 * @var string The session data key for cache array.
	 */
	protected $sessionDataKey;

	/**
	 * @var int Time in seconds till next expiration check.
	 */
	protected $secondsTillNextCheck;

	/**
	 * @var GlobalArrayContainer Global session object.
	 */
	protected $session;

	/**
	 * @param string $key The name of the session cache key.
	 * @param int $check The interval to check the cache items expiration, in seconds.
	 * @param GlobalArrayContainer Optional. Global session object.
	 */
	public function __construct($key = 'sessionCache', $check = 60, GlobalArrayContainer $container = null) {
		$this->sessionDataKey = $key;
		$this->secondsTillNextCheck = $check; //--once every 60 seconds
		$this->session = $container ?: new GlobalArrayContainer('_SESSION');
		$data = $this->export();
		if (sizeof($data)) {
			$nextCheck = $this->getNextCheck();
			if (!$nextCheck)
				$this->expirationCheck();
			else if ((int)date('YmdHis') >= (int)$nextCheck)
				$this->expirationCheck();
		}
	}

	/**
	 * Get a value.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return mixed The value from the session cache. Null if non-existent.
	 */
	public function get($key) {
		if ($this->has($key)) {
			if ($this->isValid($key)) {
				return $this->export()[$key];
			}
			else
				$this->remove($key);
		}
	}

	/**
	 * Set a value to cache.
	 * 
	 * @param string $key The variable key.
	 * @param mixed $value The value to cache.
	 * @param int $exp Optional. The expiration time in seconds. Defaults to 1 minute.
	 */
	public function set($key, $value, $exp = 60) {
		$dt = new Datetime();
		$dt->add(new DateInterval('PT' . $exp . 'S'));
		$data = $this->export(false, true);
		$data[$key] = $value;
		$data["{$key}.expiration"] = (int)$dt->format('YmdHis');
		$this->session->set($this->sessionDataKey, $data);
	}

	/**
	 * Remove a variable from cache.
	 * 
	 * @param string $key The variable key.
	 */
	public function remove($key) {
		if ($this->session->has($this->sessionDataKey)) {
			$data = $this->export(false, true);
			unset($data[$key], $data["{$key}.expiration"]);
			$this->session->set($this->sessionDataKey, $data);
		}
	}

	/**
	 * Check if a variable exists.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return bool Whether or not the varaible exists.
	 */
	public function has($key) {
		if ($this->session->has($this->sessionDataKey) && array_key_exists($key, $this->export()))
			return true;
		else
			return false;
	}

	/**
	 * Check if the variable is null.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return bool True if the variable is null, false if the variable is not null or doesn't exists.
	 */
	public function isNull($key) {
		if ($this->has($key) && $this->export()[$key] === null) {
			return true;
		}
		else
			return false;
	}

	/**
	 * Check if the variable is a empty value.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return bool True if the variable is empty, false if the varaible is not empty or doesn't exists.
	 */
	public function isEmpty($key) {
		if ($this->has($key)) {
			$value = $this->export()[$key];
			if (is_null($value) || isempty($value) || $value == '')
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Check if the varaible has expired or not.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return mixed True if the varaible is expired, false if still valid and null if the variable doesn't exists.
	 */
	public function isExpired($key) {
		if ($this->has($key)) {
			if ((int)date('YmdHis') >= (int)$this->export()["{$key}.expiration"]) {
				return true;
			}
			return false;
		}
		return null;
	}

	/**
	 * Check if the variable is valid.
	 * 
	 * @param string $key The variable key.
	 * 
	 * @return bool True if the variable is valid, false otherwise.
	 */
	public function isValid($key) {
		return $this->isExpired($key) ? false : true;
	}

	/**
	 * Renew a session variable. Can also renew an expired value before its removed.
	 * 
	 * @param string $key The variable key.
	 * @param int $renew Optional. The time in seconds to renew the value for.
	 * @param bool $expired Optional. Set to true if the variable should be renewed even if its already expired.
	 *                                Otherwise it would only renew the variable if it is still valid.
	 * 
	 * @return bool True if variable expiration has been renewed, otherwise false.
	 */
	public function renew($key, $renew = 60, $expired = false) {
		if ($this->has($key)) {
			if ($this->isValid($key) || $expired) {
				if (is_int((int)$renew)) {
					$dt = new Datetime();
					$dt->add(new DateInterval('PT' . $renew . 'S'));
					$data = $this->export(false, true);
					$data["{$key}.expiration"] = (int)$dt->format('YmdHis');
					$this->session->set($this->sessionDataKey, $data);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Check the expiration time of variable and remove them if expired.
	 */
	protected function expirationCheck() {
		$data = $this->export(false, true);
		if (sizeof($data)) {
			foreach (array_keys($data) as $key) {
				$key = preg_replace('/(.+)\.expiration$/', "$1", $key);
				if ($this->isExpired($key)) {
					$this->remove($key);
				}
			}
			$dt = new Datetime();
			$dt->add(new DateInterval('PT' . $this->secondsTillNextCheck . 'S'));
			$data['__nextCheck'] = (int)$dt->format('YmdHis');
			$this->session->set($this->sessionDataKey, $data);
		}
	}

	/**
	 * Get next check time.
	 * 
	 * @return int Next check time in 'YmdHis' format.
	 */
	public function getNextCheck() {
		$data = $this->session->get($this->sessionDataKey) ?: Array();
		return isset($data['__nextCheck']) ? $data['__nextCheck'] : null;
	}

	/**
	 * Export data, or data and next check time.
	 * 
	 * @param bool $valuesOnly Optional. Whether or not to get the values only or to include the expiration times.
	 * @param bool $nextCheck Optional. Include next check time or not.
	 * 
	 * @return Array The data.
	 */
	public function export($valuesOnly = false, $nextCheck = false) {
		$data = $this->session->get($this->sessionDataKey) ?: Array();
		if (!$nextCheck) {
			unset($data['__nextCheck']);
		}
		if ($valuesOnly) {
			foreach (array_keys($data) as $key) {
				if (preg_match('/(.+)\.expiration$/', $key)) {
					unset($data[$key]);
				}
			}
		}
		return $data;
	}

	/**
	 * Export the data in JSON format.
	 * 
	 * @return string The data in JSON format.
	 */
	public function toJSON() {
		if (version_compare(phpversion(), '5.4', '<'))
			return json_encode($this->exportWithoutClosures());
		else //--JSON_PRETTY_PRINT is available
			return json_encode($this->exportWithoutClosures(), JSON_PRETTY_PRINT);
	}

	/**
	 * Export the data in PHP serialized format.
	 * 
	 * @return stirng The data in serialized format.
	 */
	public function toSerialize() {
		return serialize($this->exportWithoutClosures());
	}

	/**
	 * Export the data in YAML format.
	 * 
	 * @return string The data in YAML format.
	 */
	public function toYAML() {
		return Yaml::dump($this->exportWithoutClosures(), 3);
	}

	/**
	 * Export the data without closures.
	 * 
	 * @return Array The data without closures.
	 */
	public function exportWithoutClosures($obj = null) {
		$obj = $obj ? $obj->export() : $this->export();
		$tmp = [];
		foreach ($obj as $key => $val) {
			if (!($val instanceof \Closure)) {
				$tmp[$key] = $val;
			}
		}
		return $tmp;
	}
}