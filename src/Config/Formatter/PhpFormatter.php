<?php
/**
 * This file is part of the BGStudios Config Component.
 *
 * (c) Brandon Garcia <brandon@bgstudios.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package BGStudios PHP Components
 * @subpackage Config
 * @author Brandon Garcia <brandon@bgstudios.io>
 */

namespace BGStudios\Component\Config\Formatter;

/**
 * PHP formatter class used for generating PHP array config files.
 */
class PhpFormatter {

	/**
	 * Formats a PHP array's values into a string.
	 *
	 * @param Array $data Array of config values.
	 * @param string $indent Current indentation.
	 * @return string The PHP array data as a string.
	 */
	protected function formatArrayNested(Array $data, $indent = "") {
		$output = '';
		$count = sizeof($data);
		$index = 0;
		$isAssoc = $this->isAssoc($data);
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

	/**
	 * Format a array into a writeable string.
	 *
	 * @param Array $data Array of config values.
	 * @param string $indent Current indentation.
	 * @return string The PHP array data as a writable string.
	 */
	public function formatArray(Array $data, $indent = '') {
		$output = $indent."[\n";
		$output .= $this->formatArrayNested($data, $indent."\t");
		$output .= $indent."]";
		return $output;
	}

	/**
	 * Determine if array is an associative array, mixed arrays included.
	 *
	 * @param Array $a The array to test.
	 * @return bool true if array is assoc/mixed or false otherwise.
	 */
	protected function isAssoc(Array $a) {
		foreach (array_keys($a) as $k)
			if (!is_int($k)) return true;
		return false;
	}
}