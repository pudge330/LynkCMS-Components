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

namespace LynkCMS\Component\Form\Validator;

/**
 * Basic data validator class.
 */
class BasicDataValidator {

	/**
	 * @var Array List of supported file mimes and extensions.
	 */
	protected static $fileIndex = array();

	/**
	 * Validate email address.
	 * 
	 * @param string $email Email address.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function email($email) {
		return (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			? false : true;
	}

	/**
	 * Validate phone number.
	 * 
	 * @param string $phone Phone number.
	 * @param string $type Optional. Validate US or a more basic type.
	 *                               Basiuc type checks for characters and minimum length of 10.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function phone($phone, $type = 'us') {
		switch ($type) {
			case 'us':
				return (preg_match('/^\+?\d{0,5}(?:[\s-\.])?\(?\d{3}\)?(?:[\s-\.])?\d{3}(?:[\s-\.])?\d{4}$/', $phone))
					? true : false;
				break;
			case 'basic':
				$passedCharacterTest = preg_match('/^[0-9-\.\+ ]+$/', $phone);
				preg_match_all('/\d/', $phone, $numericalMatches);
				$passedNumberMinimumTest = sizeof($numericalMatches[0]) >= 10 ? true : false;
				return ($passedCharacterTest && $passedNumberMinimumTest);
				break;
			default:
				return true;
		}
	}

	/**
	 * Validate text.
	 * 
	 * @param string $text Text.
	 * @param int $max Optional. Max length.
	 * @param int $min Optional. Min length.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function text($text, $max = null, $min = null) {
		$passedMinTest = $min === null || strlen($text) >= $min ? true : false;
		$passedMaxTest = $max === null || strlen($text) <= $max ? true : false;
		return ($passedMinTest && $passedMaxTest);
	}

	/**
	 * Validate rgb color.
	 * change: add support for transparancy 'rgb(*, *, *, *)'.
	 * 
	 * @param mixed $rgb RGB color. CSS format 'rgb(*, *, *)' or array of rgb values.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function rgb($rgb) {
		if (!$rgb)
			return false;
		if (is_string($rgb)) {
			if (preg_match('/(?:rgb)?\(?([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3}),([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3}),([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3})\)?$/', $rgb)) {
				return true;
			}
			else
				return false;
		}
		else {
			$rgb = array_values($rgb);
			$passedLengthTest = sizeof($rgb) == 3;
			$passedRangeTest = true;
			for ($i = 0; $i < sizeof($rgb); $i++) {
				if ((!is_numeric($rgb[$i]) || $rgb[$i] === null) || (int)$rgb[$i] < 0 || (int)$rgb[$i] > 255)
					$passedRangeTest = false;
			}
			return ($passedLengthTest && $passedRangeTest);
		}
	}

	/**
	 * Validate CSS rgb color.
	 * change: add support for transparancy 'rgb(*, *, *, *)'.
	 * 
	 * @param string $rgb RGB color. CSS format 'rgb(*, *, *)'.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function cssRgb($rgb) {
		$rgb = str_replace(' ', '', $rgb);
		//--strict 0-255 enforced
		return preg_match('/^rgb\(([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3}),([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3}),([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3})\)$/', $rgb);
		// return preg_match('/^rgb\(\d{1,3},\d{1,3},\d{1,3}\)$/', $rgb);
	}

	/**
	 * Validate hex color.
	 * 
	 * @param string $hex Hex color.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function hexColor($hex) {
		$rgb = \lynk\hexToRgb($hex);
		$passedConversionTest = $rgb[0] !== null;
		$passedRgbTest = $this->rgb($rgb);
		return ($passedConversionTest && $passedRgbTest);
	}

	/**
	 * Validate integer.
	 * 
	 * @param mixed $int Integer value.
	 * @param Array $range Optional. Array of min and max values.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function int($int, $range = ['min' => null, 'max' => null]) {
		$range['min'] = isset($range['min']) ? $range['min'] : null;
		$range['max'] = isset($range['max']) ? $range['max'] : null;
		$passedNumericalTest = is_numeric($int);
		$passedIntegerTest = is_int((int)$int);
		$passedMinTest = $range['min'] === null || ($passedNumericalTest && (int)$int >= $range['min']);
		$passedMaxTest = $range['max'] === null || ($passedNumericalTest && (int)$int <= $range['max']);
		return ($passedNumericalTest && $passedIntegerTest && $passedMinTest && $passedMaxTest);
	}

	/**
	 * Validate double.
	 * 
	 * @param mixed $double $double Double value.
	 * @param Array $range Optional. Array of min and max values.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function double($double, $range = ['min' => null, 'max' => null]) {
		$range['min'] = isset($range['min']) ? $range['min'] : null;
		$range['max'] = isset($range['max']) ? $range['max'] : null;
		$passedNumericalTest = is_numeric($double);
		$passedDoubleTest = is_double((double)$double);
		$passedMinTest = $range['min'] === null || ($passedNumericalTest && (double)$double >= $range['min']);
		$passedMaxTest = $range['max'] === null || ($passedNumericalTest && (double)$double <= $range['max']);
		return ($passedNumericalTest && $passedDoubleTest && $passedMinTest && $passedMaxTest);
	}

	/**
	 * Validate float.
	 * 
	 * @param mixed $float $float Double value.
	 * @param Array $range Optional. Array of min and max values.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function float($float, $range = ['min' => null, 'max' => null]) {
		$doubleTest = $this->double($float, $range);
		$floatTest = is_float((float)$float);
		return ($doubleTest && $floatTest);
	}

	/**
	 * Validate file.
	 * 
	 * @param string $path File path.
	 * @param mixed $accepted Array of accepted file types or '*' for any.
	 * @param int $maxSize Optional. Max file size in bytes.
	 * @param string $ext Optional. Manually pass file extension.
	 * @param bool $extOnly Optional. Only check file extension.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function file($path, $accepted, $maxSize = null, $ext = null, $extOnly = false) {
		$ext = $ext ?: strtolower(pathinfo($path, PATHINFO_EXTENSION));
		$type = \lynk\mimeType($path);
		$accepted = !$accepted ? '*' : $accepted;
		$accepted = explode(',', $accepted);
		$passedAcceptedTest = false;
		if ($accepted[0] == '*') {
			$passedAcceptedTest = true;
		}
		else if (sizeof(static::$fileIndex) === 0) {
			//--no file index, only check ext
			$passedAcceptedTest = in_array($ext, $accepted);
		}
		else {
			for ($i = 0; $i < sizeof($accepted); $i++) {
				$fileIndexInfo = self::getFileIndexInfo($accepted[$i]);
				if ($fileIndexInfo) {
					if (in_array($ext, $fileIndexInfo['exts']) && in_array($type, $fileIndexInfo['types'])) {
						$passedAcceptedTest = true;
					}
					else if ($extOnly && in_array($ext, $fileIndexInfo['exts'])) {
						$passedAcceptedTest = true;
					}
				}
			}
		}
		$passedMaxTest = $maxSize && filesize($path) <= $maxSize || $maxSize === null;
		return ($passedAcceptedTest && $passedMaxTest);
	}

	/**
	 * Validate file extension.
	 * 
	 * @param string $path File path.
	 * @param mixed $accepted Array of accepted file types or '*' for any.
	 * @param int $maxSize Optional. Max file size in bytes.
	 * @param string $ext Optional. Manually pass file extension.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function fileExts($path, $accepted, $maxSize = null, $ext = null) {
		return $this->file($path, $accepted, $maxSize, $ext, true);
	}

	/**
	 * Get supported file index.
	 * 
	 * @return Array Supported file index.
	 */
	public static function getFileIndex() {
		return static::$fileIndex;
	}

	/**
	 * Set supported file index.
	 * 
	 * @param Array $index Supported file index.
	 */
	public static function setFileIndex($index) {
		static::$fileIndex = $index;
	}

	/**
	 * Return file index information. Mimes and extensions.
	 * 
	 * @param string $type File type.
	 * 
	 * @return Array File index info or null if type does not exists.
	 */
	public static function getFileIndexInfo($type) {
		if (isset(static::$fileIndex[$type]))
			return static::$fileIndex[$type];
		else
			return null;
	}
}