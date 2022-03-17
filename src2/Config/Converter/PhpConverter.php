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
 * Handles converting and parsing data from PHP array format.
 */
class PhpConverter implements ConverterInterface {

	/**
	 * Get data extension, if multiple primary first.
	 * 
	 * @return Array List of file extensions.
	 */
	public function extensions() {
		return ['php'];
	}

	/**
	 * Read data file.
	 *
	 * @param string $file The file path
	 * 
	 * @return string The file data
	 */
	public function read(string $path) {
		return include $path;
	}

	/**
	 * Convert data to PHP array format.
	 *
	 * @param array $data Array of data
	 * 
	 * @return string The converted PHP data
	 */
	public function dump(array $data) {
		$output = $indent."[\n";
		$output .= $this->formatArrayNested($data, $indent."\t");
		$output .= $indent."]";
		return "<?php\nreturn " . $output . ";";
	}

	/**
	 * Parse data.
	 *
	 * @param string The data string
	 * 
	 * @return array The parsed data
	 */
	public function parse(array $data) {
		return $data;
	}

	/**
	 * Formats a PHP array's values into a string.
	 *
	 * @param array $data Array of config values.
	 * @param string $indent Current indentation.
	 * 
	 * @return string The PHP array data as a string.
	 */
	protected function formatArrayNested(array $data, $indent = "") {
		$output = '';
		$count = sizeof($data);
		$index = 0;
		$isAssoc = \lynk\isAssoc($data);
		foreach ($data as $k => $v) {
			if (is_string($v))
				$output .= $indent.($isAssoc ? "'$k' => " : "")."'".str_replace("'", "\\'", $v)."'";
			else if (is_bool($v)) 
				$output .= $indent.($isAssoc ? "'$k' => " : "").($v ? 'true' : 'false')."";
			else if (is_numeric($v))
				$output .= $indent.($isAssoc ? "'$k' => " : "").$v;
			else if (is_null($v))
				$output .= $indent.($isAssoc ? "'$k' => " : "")."null";
			else if (is_array($v)) {
				$output .= $indent.($isAssoc ? "'$k' => " : "")."[\n";
				$output .= $this->formatArrayNested($v, $indent."\t");
				$output .= "".$indent."]";
			}
			else if (is_object($v)) {
				$output .= $indent.($isAssoc ? "'$k' => " : "").'unserialize(str_replace([\'\"\'], [\'"\'], "'.str_replace(['"'], ['\"'], serialize($v)).'"))';
			}
			if ($index++ < $count - 1)
				$output .= ",\n";
			else
				$output .= "\n";
		}
		return $output;
	}
}