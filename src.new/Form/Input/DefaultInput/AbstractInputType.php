<?php
namespace LynkCMS\Component\Form\Input\DefaultInput;

use Datetime;
use Exception;
use LynkCMS\Component\Form\Input\InputType;

class AbstractInputType extends InputType {
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