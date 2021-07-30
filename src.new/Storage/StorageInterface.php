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

/**
 * Interface for key value storage mechanism.
 */
interface StorageInterface {

    /**
     * Get value(s).
     * 
     * @param mixed $key Either a string or array of string keys.
     * 
     * @return mixed Value or array of values.
     */
	public function get($key);

    /**
     * Set value(s).
     * 
     * @param mixed $key A key as a string or an array of key value pairs.
     * @param mixed $value The value to store or null if array is passsed for $key.
     * 
     * @return bool True if successful or false if not.
     */
	public function set($key, $value = null);

    /**
     * Check whether or not a key exists.
     * 
     * @param string $key Key to check for.
     * 
     * @return bool True if key exists, false if not.
     */
	public function has($key);

    /**
     * Remove a value from storage.
     * 
     * @param string $key The key of the value to be removed.
     * 
     * @return bool True if successfully removed the value, false if not.
     */
	public function remove($key);
}