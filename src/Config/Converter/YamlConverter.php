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

use Symfony\Component\Yaml\Yaml;

/**
 * Handles converting and parsing data from YAML format.
 */
class YamlConverter implements ConverterInterface {

	/**
	 * Get data extension, if multiple primary first.
	 * 
	 * @return Array List of file extensions.
	 */
	public function extensions() {
		return ['yml', 'yaml'];
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
			$data = '{}';
		}
		return $data;
	}

	/**
	 * Convert data to YAML format.
	 *
	 * @param array $data Array of data
	 * 
	 * @return string The converted data
	 */
	public function dump(array $data) {
		return Yaml::dump($data, 200);
	}

	/**
	 * Parse YAML data.
	 *
	 * @param string The data string
	 * 
	 * @return array The parsed data
	 */
	public function parse(string $data) {
		return Yaml::parse($data);
	}
}