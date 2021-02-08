<?php
namespace LynkCMS\Component\Form\Validator;

class BasicDataValidator {
	protected static $fileIndex = array();
	public function email($email) {
		return (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
			? false : true;
	}
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
	public function text($text, $max = null, $min = null) {
		$passedMinTest = $min === null || strlen($text) >= $min ? true : false;
		$passedMaxTest = $max === null || strlen($text) <= $max ? true : false;
		return ($passedMinTest && $passedMaxTest);
	}
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
	public function cssRgb($rgb) {
		$rgb = str_replace(' ', '', $rgb);
		//--strict 0-255 enforced
		return preg_match('/^rgb\(([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3}),([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3}),([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5]{1,3})\)$/', $rgb);
		// return preg_match('/^rgb\(\d{1,3},\d{1,3},\d{1,3}\)$/', $rgb);
	}
	public function hexColor($hex) {
		$rgb = $this->hexToRgb($hex);
		$passedConversionTest = $rgb[0] !== null;
		$passedRgbTest = $this->rgb($rgb);
		return ($passedConversionTest && $passedRgbTest);
	}
	public function int($int, $range = ['min' => null, 'max' => null]) {
		$range['min'] = isset($range['min']) ? $range['min'] : null;
		$range['max'] = isset($range['max']) ? $range['max'] : null;
		$passedNumericalTest = is_numeric($int);
		$passedIntegerTest = is_int((int)$int);
		$passedMinTest = $range['min'] === null || ($passedNumericalTest && (int)$int >= $range['min']);
		$passedMaxTest = $range['max'] === null || ($passedNumericalTest && (int)$int <= $range['max']);
		return ($passedNumericalTest && $passedIntegerTest && $passedMinTest && $passedMaxTest);
	}
	public function double($double, $range = ['min' => null, 'max' => null]) {
		$range['min'] = isset($range['min']) ? $range['min'] : null;
		$range['max'] = isset($range['max']) ? $range['max'] : null;
		$passedNumericalTest = is_numeric($double);
		$passedDoubleTest = is_double((double)$double);
		$passedMinTest = $range['min'] === null || ($passedNumericalTest && (double)$double >= $range['min']);
		$passedMaxTest = $range['max'] === null || ($passedNumericalTest && (double)$double <= $range['max']);
		return ($passedNumericalTest && $passedDoubleTest && $passedMinTest && $passedMaxTest);
	}
	public function file($path, $accepted, $maxSize = null, $ext = null, $extOnly = false) {
		$ext = $ext ?: strtolower(pathinfo($path, PATHINFO_EXTENSION));
		$type = $this->getFileMimeType($path);
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
	public function fileExts($path, $accepted, $maxSize = null, $ext = null) {
		return $this->file($path, $accepted, $maxSize, $ext, true);
	}
	public static function getFileIndex() {
		return static::$fileIndex;
	}
	public static function setFileIndex($index) {
		static::$fileIndex = $index;
	}
	public static function getFileIndexInfo($type) {
		if (isset(static::$fileIndex[$type]))
			return static::$fileIndex[$type];
		else
			return null;
	}
	protected function getFileMimeType($file) {
		$fileExists = file_exists($file);
		if(function_exists('mime_content_type'))
			return mime_content_type($file);
		else if ($fileExists && function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mimetype;
		}
		else {
			$file = escapeshellcmd($file);
			$command = "file -b --mime-type -m /usr/share/misc/magic {$file}";
			$mimeType = shell_exec($command);
			return trim($mimeType);
		}
	}

	protected function hexToRgb($hex, $assoc = false) {
		$hex = str_replace("#", "", $hex);
		$r = $g = $b = null;
		if ($hex && preg_match('/^[0-9a-f]+$/i', $hex)) {
			if(strlen($hex) == 3) {
				$r = hexdec($hex[0].$hex[0]);
				$g = hexdec($hex[1].$hex[1]);
				$b = hexdec($hex[2].$hex[2]);
			} else if (strlen($hex) == 6) {
				$r = hexdec($hex[0].$hex[1]);
				$g = hexdec($hex[2].$hex[3]);
				$b = hexdec($hex[4].$hex[5]);
			}
		}
		return [$r, $g, $b];
	}
}