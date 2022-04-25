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
 * @subpackage Form
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\FormNew2;

/**
 * Form error class.
 */
class FormError {

	/**
	 * @var string Error message.
	 */
	protected $error;

	/**
	 * @var string Error message key/name.
	 */
	protected $key;

	/**
	 * @param string Error key if message is second argument, otherwise error message.
	 * @param string Optional. Error message.
	 */
	public function __construct() {
		$args = func_get_args();
		switch (sizeof($args)) {
			case 1:
				$this->key = substr(md5($args[0]), 0, 16);
				$this->error = $args[0];
			break;
			case 2:
				$this->key = preg_replace('/([^a-zA-Z0-9-_])/', '_', $args[0]);
				$this->error = $args[1];
			break;
			default:
				throw new Exception('Invalid number of arguments.');
			break;
		}
	}

	/**
	 * Get the error message.
	 * 
	 * @return string Error message.
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Set the error message.
	 * 
	 * @param string $error Error message.
	 */
	public function setError($error) {
		$this->error = $error;
	}

	/**
	 * Get error message key.
	 * 
	 * @return string Error key.
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Set error message key.
	 * 
	 * @param string $key Error key.
	 */
	public function setKey($key) {
		$this->key = $key;
	}

	/**
	 * Render html error message.
	 * 
	 * @param string $type Optional. Element type.
	 * 
	 * @return string Rendered html error code.
	 */
	public function render($type = null) {
		if (!$type) {
			$type = 'p';
		}
		$output = '';
		switch ($type) {
			case 'list':
			case 'ul-list':
			case 'ol-list':
				$output = "<li class=\"formError formError_{$this->key}\">".htmlentities($this->error)."</li>\n";
			break;
			default:
				$output = "<{$type} class=\"formError formError_{$this->key}\">".htmlentities($this->error)."</{$type}>\n";
			break;
		}
		return $output;
	}
}