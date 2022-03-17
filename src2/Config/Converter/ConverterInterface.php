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
 * Handles converting and parsing data from different formats.
 */
interface ConverterInterface {

	/**
	 * Get data extension, if multiple primary first.
	 * 
	 * @return Array List of file extensions.
	 */
	public function extensions();

	/**
	 * Read data file.
	 *
	 * @param string $file The file path
	 * 
	 * @return string The file data
	 */
	public function read(string $path);

	/**
	 * Convert data.
	 *
	 * @param array $data Array of data
	 * 
	 * @return string The converted data
	 */
	public function dump(array $data);

	/**
	 * Parse data.
	 *
	 * @param string The data string
	 * 
	 * @return array The parsed data
	 */
	public function parse(array $data);
}