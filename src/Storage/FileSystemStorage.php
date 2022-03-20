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
 * @subpackage Storage
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Storage;

use Exception;

/**
 * File system key value storage.
 */
class FileSystemStorage implements StorageInterface {

	/**
	 * @var string Directory path for file storage.
	 */
	protected $directory;

	/**
	 * @param string $directory Directory path for file storage.
	 * 
	 * @throws \Exception
	 */
	public function __construct($directory = null) {
			if (!$directory) {
				$directory = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : null;
			}
			if (!$directory || !file_exists($directory)) {
				throw new \Exception('Lynk\\Component\\Storage\\FileSystemStorage expects either a valid directory or for $_SERVER[\'DOCUMENT_ROOT\'] to be set');
			}
	}

	/**
     * Get value(s).
     * 
     * @param mixed $key Either a string or array of string keys.
     * 
     * @return mixed Value or array of values.
     */
	public function get($key) {
		if (is_array($key)) {
			$final = Array();
			foreach ($key as $k) {
				$final[$k] = $this->get($k);
			}
			return $final;
		}
		else if (file_exists("{$this->directory}/{$key}")) {
			return file_get_contents("{$this->directory}/{$key}");
		}
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
		if (is_array($key)) {
			$result = true;
			foreach($key as $k => $v) {
				$result = ($result && $this->set($k, $v));
			}
			return $result;
		}
		else {
			return (file_put_contents("{$this->directory}/{$key}", $value));
		}
	}

	/**
     * Check whether or not a key exists.
     * 
     * @param string $key Key to check for.
     * 
     * @return bool True if key exists, false if not.
     */
	public function has($key) {
		return (file_exists("{$this->directory}/{$key}"));
	}

	/**
     * Remove a value from storage.
     * 
     * @param string $key The key of the value to be removed.
     * 
     * @return bool True if successfully removed the value, false if not.
     */
	public function remove($key) {
		if (file_exists("{$this->directory}/{$key}")) {
			return (unlink("{$this->directory}/{$key}"));
		}
		return false;
	}
}