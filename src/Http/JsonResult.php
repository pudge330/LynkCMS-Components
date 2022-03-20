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
 * @subpackage Http
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Http;

use JsonSerializable;

/**
 * JSON http result. Standardized result structure.
 */
class JsonResult implements JsonSerializable {

	/**
	 * @var string Response status.
	 */
	protected $status;

	/**
	 * @var string Response message.
	 */
	protected $message;

	/**
	 * @var string The data payload key.
	 */
	protected $payloadKey;

	/**
	 * @var bool Whether or not to format JSON nicely with tabs, newlines and indentation.
	 */
	protected $prettyPrint;

	/**
	 * @param Array $data Optional. Resposne data.
	 *                              Special keys for including status and message. `_status` and `_message` respectively.
	 * @param string $payloadKey Optional. The data payload key.
	 * @param bool $prettyPrint Optional. Whether or not to format JSON nicely with tabs, newlines and indentation.
	 */
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

	/**
	 * Set response status.
	 * 
	 * @param string $status Resposne status.
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * Get response status.
	 * 
	 * @return string Response status.
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Set response message.
	 * 
	 * @param string $msg Response message.
	 */
	public function setMessage($msg) {
		$this->message = $msg;
	}

	/**
	 * Get response message.
	 * 
	 * @return string Response message.
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Set data.
	 * 
	 * @param string $key Data key.
	 * @param mixed $value Data value.
	 */
	public function setData($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * Get data.
	 * 
	 * @param string $key Data key.
	 * 
	 * @return mixed Data value.
	 */
	public function getData($key) {
		if ($this->hasData($key))
			return $this->data[$key];
		return null;
	}

	/**
	 * Has data.
	 * 
	 * @param string $key Data key.
	 * 
	 * @return bool True if key exists, false if not.
	 */
	public function hasData($key) {
		return array_key_exists($key, $this->data);
	}

	/**
	 * Json encode response.
	 * 
	 * @return string Encoded json response.
	 */
	public function encode() {
		return json_encode($this->export(), JSON_PRETTY_PRINT);
	}

	/**
	 * Export response.
	 * 
	 * @return Array Response as array.
	 */
	public function export() {
		return Array(
			'status' => $this->getStatus()
			,'message' => $this->getMessage()
			,$this->payloadKey => $this->data
		);
	}

	/**
	 * Return data to be serialized.
	 * 
	 * @return Array Data to serialize.
	 */
	public function jsonSerialize() {
		return $this->export();
	}
}