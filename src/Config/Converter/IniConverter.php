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
 * Handles converting and parsing data from ini format.
 */
class IniConverter implements ConverterInterface {

	/**
	 * Get data extension, if multiple primary first.
	 * 
	 * @return Array List of file extensions.
	 */
	public function extensions() {
		return ['ini'];
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
	 * Convert data to ini format.
	 *
	 * @param array $array Array of data
	 * 
	 * @return string The converted ini config
	 */
	public function dump(array $data) {
		$isNumber = function($value) {
			return (is_numeric($value) && is_string($value) === false);
		};
		$data = Array();
		foreach ($data as $key => $val) {
			if (is_array($val)) {
				$data[] = "[$key]";
				foreach ($val as $skey => $sval) {
					if (is_array($sval)) {
						foreach ($sval as $_skey => $_sval) {
							if (is_numeric($_skey)) {
								$data[] = $skey.'[] = '.($isNumber($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
							} else {
								$data[] = $skey.'['.$_skey.'] = '.($isNumber($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
							}
						}
					} else {
						$data[] = $skey.' = '.($isNumber($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
					}
				}
			} else {
				$data[] = $key.' = '.($isNumber($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
			}
			$data[] = null;
		}
		return implode(PHP_EOL, $data) . PHP_EOL;
	}

	/**
	 * Parse an ini data string.
	 *
	 * @param string The ini data string
	 * 
	 * @return array The parsed data
	 */
	public function parse(string $data) {
		return parse_ini_string($data, true, INI_SCANNER_TYPED);
	}
}