<?php
namespace BGStudios\Component\Http;

use JsonSerializable;

class JsonResult implements JsonSerializable {
	protected $status;
	protected $message;
	protected $payloadKey;
	protected $prettyPrint;
	public function __construct($data = Array(), $payloadKey = 'payload', $prettyPrint = false) {
		if (array_key_exists('_status', $data)) {
			$this->setStatus($data['_status']);
			unset($data['_status']);
		}
		else
			$this->setStatus(null);
		if (array_key_exists('_message', $data)) {
			$this->setMessage($data['_message']);
			unset($data['_message']);
		}
		else
			$this->setMessage(null);
		$this->data = $data;
		$this->payloadKey = $payloadKey;
		$this->prettyPrint = $prettyPrint;
	}
	public function setStatus($status) {
		$this->status = $status;
	}
	public function getStatus() {
		return $this->status;
	}
	public function setMessage($msg) {
		$this->message = $msg;
	}
	public function getMessage() {
		return $this->message;
	}
	public function setData($key, $value) {
		$this->data[$key] = $value;
	}
	public function getData($key) {
		if ($this->hasData($key))
			return $this->data[$key];
		return null;
	}
	public function hasData($key) {
		return array_key_exists($key, $this->data);
	}
	public function encode() {
		return json_encode($this->export(), JSON_PRETTY_PRINT);
	}
	public function export() {
		return Array(
			'status' => $this->getStatus()
			,'message' => $this->getMessage()
			,$this->payloadKey => $this->data
		);
	}
	public function jsonSerialize() {
		return $this->export();
	}
}