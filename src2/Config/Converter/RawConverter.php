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
 * @subpackage Config
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Config\Converter;

/**
 * Handles converting and parsing data from RAW format.
 */
class RawConverter implements ConverterInterface {

	/**
	 * Get data extension, if multiple primary first.
	 * 
	 * @return Array List of file extensions.
	 */
	public function extensions() {
		return ['txt', 'raw'];
	}

	/**
	 * Read data file.
	 *
	 * @param string $file The file path
	 * 
	 * @return string The file data
	 */
	public function read(string $path) {
		return file_get_contents($path);
	}

	/**
	 * Convert data to RAW format.
	 *
	 * @param array $data Array of data
	 * 
	 * @return string The converted JSON data
	 */
	public function dump(array $data) {
		if (gettype($data) != 'string') {
			$data = "[serialized]" . serialize($data);
		}
		return $data;
	}

	/**
	 * Parse RAW data.
	 *
	 * @param string The data string
	 * 
	 * @return array The parsed data
	 */
	public function parse(array $data) {
		if (\lynk\startsWith($data, '[serialized]')) {
			$data = unserialize(substr($data, 12));
		}
		return $data;
	}
}