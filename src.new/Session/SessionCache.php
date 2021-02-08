<?php
namespace LynkCMS\Component\Session;

use Datetime;
use DateInterval;
use LynkCMS\Component\Storage\GlobalAccessContainer;

class SessionCache {
	protected $sessionDataKey;
	protected $secondsTillNextCheck;
	protected $session;
	public function __construct($name = 'sessionCache', $check = 60, GlobalAccessContainer $container = null) {
		$this->sessionDataKey = $name;
		$this->secondsTillNextCheck = $check; //--once every 60 seconds
		$this->session = $container ?: new GlobalAccessContainer('_SESSION');
		$data = $this->export();
		if (sizeof($data)) {
			$nextCheck = $this->getNextCheck();
			if (!$nextCheck)
				$this->expirationCheck();
			else if ((int)date('YmdHis') >= (int)$nextCheck)
				$this->expirationCheck();
		}
	}
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
	public function get($key) {
		if ($this->has($key)) {
			if ($this->isValid($key)) {
				return $this->export()[$key];
			}
			else
				$this->remove($key);
		}
	}
	public function set($key, $value, $exp = 60) {
		$dt = new Datetime();
		$dt->add(new DateInterval('PT' . $exp . 'S'));
		$data = $this->export(false, true);
		$data[$key] = $value;
		$data["{$key}.expiration"] = (int)$dt->format('YmdHis');
		$this->session->set($this->sessionDataKey, $data);
	}
	public function remove($key) {
		if ($this->session->has($this->sessionDataKey)) {
			$data = $this->export(false, true);
			unset($data[$key], $data["{$key}.expiration"]);
			$this->session->set($this->sessionDataKey, $data);
		}
	}
	public function has($key) {
		if ($this->session->has($this->sessionDataKey) && array_key_exists($key, $this->export()))
			return true;
		else
			return false;
	}
	public function isExpired($key) {
		if ($this->has($key)) {
			if ((int)date('YmdHis') >= (int)$this->export()["{$key}.expiration"]) {
				return true;
			}
			return false;
		}
		return null;
	}
	public function isValid($key) {
		return $this->isExpired($key) ? false : true;
	}
	public function isnull($key) {
		if ($this->has($key) && $this->export()[$key] === null) {
			return true;
		}
		else
			return false;
	}
	public function isempty($key) {
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
	public function getNextCheck() {
		$data = $this->session->get($this->sessionDataKey) ?: Array();
		return isset($data['__nextCheck']) ? $data['__nextCheck'] : null;
	}
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
	public function toJSON() {
		if (version_compare(phpversion(), '5.4', '<'))
			return json_encode($this->exportWithoutClosures());
		else //--JSON_PRETTY_PRINT is available
			return json_encode($this->exportWithoutClosures(), JSON_PRETTY_PRINT);
	}
	public function toSerialize() {
		return serialize($this->exportWithoutClosures());
	}
	public function toYAML() {
		return Yaml::dump($this->exportWithoutClosures(), 3);
	}
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