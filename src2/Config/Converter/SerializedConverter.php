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
 * Handles converting and parsing data from serialized format.
 */
class SerializedConverter implements ConverterInterface {

	/**
	 * Get data extension, if multiple primary first.
	 * 
	 * @return Array List of file extensions.
	 */
	public function extensions() {
		return ['serialized', 'object'];
	}

	/**
	 * Read data file.
	 *
	 * @param string $file The file path
	 * 
	 * @return string The file data
	 */
	public function read(string $path) {
		$data = file_get_contents($path);
		if (trim($data) === '') {
			$data = 's:0:"";';
		}
		return $data;
	}

	/**
	 * Convert data to serialized format.
	 *
	 * @param array $data Array of data
	 * 
	 * @return string The converted data
	 */
	public function dump(array $data) {
		return serialize($data);
	}

	/**
	 * Parse serialized data.
	 *
	 * @param string The data string
	 * 
	 * @return array The parsed data
	 */
	public function parse(array $data) {
		return unserialize($data);
	}
}