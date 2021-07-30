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
 * @subpackage Form
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Form\Input\Type;

use Datetime;
use Exception;
use LynkCMS\Component\Form\Input\InputType;

/**
 * Abstract input type.
 */
class AbstractInputType extends InputType {

	/**
	 * Convert string to DateTime object.
	 * 
	 * @param string $str String representation of a date or time value.
	 * @param string $format Optional. Date or time format.
	 * 
	 * @return DateTime DateTime object or null on failure.
	 */
	protected function convertToDatetime($str, $format = null) {
		$dt = null;
		try {
			$dt = new Datetime($str);
		}
		catch (Exception $e) {
			$dt = null;
		}
		if (!$dt && $format) {
			try {
				$dt = Datetime::createFromFormat($format, $str);
			}
			catch (Exception $e) {
				$dt = null;
			}
		}
		return $dt;
	}

	/**
	 * Auto-convert string to DateTime object. Trying out various formats.
	 * 
	 * @param string $str String representation of a date or time value.
	 * @param string $format Optional. Date or time format.
	 * 
	 * @return DateTime DateTime object or null on failure.
	 */
	protected function autoConvertToDatetime($str) {
		$formats = array(
			'/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9])$/' => 'Y-m-d'
			,'/^(?:[0-2][0-9]):(?:[0-5][0-9])$/' => 'H:i'
			,'/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9]) (?:[0-2][0-9]):(?:[0-5][0-9])$/' => 'Y-m-d H:i'
			
		);
		if (preg_match('/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9]) (?:[0-2][0-9]):(?:[0-5][0-9])$/', $str)) {
			$dt = new Datetime($str);
		}
		$dt = null;
		try {
			$dt = new Datetime($str);
		}
		catch (Exception $e) {
			$dt = null;
		}
		if (!$dt && $format) {
			try {
				$dt = Datetime::createFromFormat($format, $str);
			}
			catch (Exception $e) {
				$dt = null;
			}
		}
		return $dt;
	}
}