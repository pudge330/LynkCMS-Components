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
 * @subpackage Form
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Form\Input;

use DateTime;
use Lynk\Component\Form\Validator\BasicDataValidator;
use Lynk\Component\Util\NamedBuffers;

/**
 * Form input helper, contains useful functions for input processing/rendering.
 *
 * @author Brandon Garcia <me@bgarcia.dev>
 */
class InputHelper {

	/**
	 * @var BasicDataValidator Basic data validator class.
	 */
	public $validator;

	/**
	 * @var NamedBuffers Named output buffer class.
	 */
	public $buffer;

	/**
	 * @param BasicDataValidator Basic data validator instance.
	 */
	public function __construct(BasicDataValidator $validator = null) {
		$this->validator = new BasicDataValidator();
		$this->buffer = new NamedBuffers();
	}

	/**
	 * Generic data validation based on simple types.
	 * 
	 * @param string $allow Data type.
	 * @param mixed $data Data value.
	 * 
	 * @return bool True if valid, false otherwise.
	 */
	public function validateType($allow, $data) {
		if ($allow && in_array($allow, ['email', 'phone', 'text', 'int', 'double', 'rgb', 'hexcolor'])) {
			return $this->validator->{$allow}($data);
		}
		return true;
	}

	/**
	 * Validate data exists in source.
	 * 
	 * @param array|string $names Data value names/keys.
	 * @param array $source Source array.
	 * 
	 * @return bool True if exists, false otherwise.
	 */
	public function validateExists($names, array $source) {
		$source = array_keys($source);
		$names = is_array($names) ? $names : [$names];
		foreach ($names as $name) {
			if (!in_array($name, $source))
				return false;
		}
		return true;
	}

	/**
	 * Convert CSV data line to input options.
	 * 
	 * @param string $data CSV data line.
	 * @param string $form Optional. Data format.
	 * 
	 * @return Array Option data.
	 */
	public function processCsv($data, $format = null) {
		if (is_array($data))
			return $data;
		if (!is_array($data)) {
			$data = explode(',', $data);
		}
		$max = sizeof($data);
		$values = [];
		for ($i = 0; $i < $max; $i++) {
			
			$tmpKey = $tmpValue = '';
			if (preg_match('/kv$/', $format)) {
				$tmpKey = $i;
				$tmpValue = $data[$i];
			}
			else {
				$tmpKey = $tmpValue = $data[$i];
			}
			if (strpos($data[$i], '|') !== false) {
				$tmpVal = explode('|', $data[$i]);
				$tmpKey = $tmpVal[0];
				$tmpValue = $tmpVal[1];
			}
			$values[$tmpKey . ''] = $tmpValue;
		}
		$data = $values;
		return $data;
	}

	/**
	 * Convert data from source file.
	 * 
	 * @param string $file File path.
	 * @param string $format Optional. Data format.
	 * 
	 * @return Array Options data.
	 */
	public function processDataFile($file, $format = null) {
		$returnData = [];
		if (!file_exists($file) || is_dir($file))
			return $returnData;
		if ($format == 'config') {
			$data = $this->getDataFile($file, $format);
			$tmpData = [];
			foreach ($data as $k => $v) {
				$tmpData[$v . ''] = $v;
			}
			$returnData = $tmpData;
		}
		else if ($format == 'config-kv') {
			$data = $this->getDataFile($file, $format);
			foreach ($data as $k => $v) {
				$tmpData[$k . ''] = $v;
			}
			$returnData = $tmpData;
		}
		else if ($format == 'lsv' || $format == 'lsv-kv') {
			$data = file_get_contents($file);
			$data = str_replace("\n\n", "\n", $data);
			$data = explode("\n", $data);
			$returnData = $data;
			$returnData = $this->processCsv($returnData, $format);
		}
		else {
			$returnData = file_get_contents($file);
			$returnData = $this->processCsv($returnData, $format);
		}
		return $returnData;
	}

	/**
	 * Get data from file.
	 * 
	 * @param string $file File path.
	 * @param string $format Data format.
	 * @param string $type Optional. Data file type.
	 * 
	 * @param Array Options data.
	 */
	public function getDataFile($file, $format, $type = null) {
		$type = $type ?: strtolower(pathinfo($file, PATHINFO_EXTENSION));
		$fileExists = file_exists($file);
		$data = '';
		switch($type) {
			case 'php':
				if ($fileExists)
					$data = include $file;
				break;
			case 'json':
				if ($fileExists) {
					$data = file_get_contents($file);
					$data = json_decode($data, true);
				}
				break;
			case 'object':
			case 'data':
				if ($fileExists) {
					$data = file_get_contents($file);
					$data = unserialize($data);
				}
				break;
			case 'csv':
				if ($fileExists) {
					$data = file_get_contents($file);
					$data = $this->processCsv($data, $format);
				}
				break;
		}
		return $data;
	}

	/**
	 * Get default value(s) for input. Convert to array if multiple.
	 * 
	 * @param StandardContainer $settings Field settings.
	 * @param Array $values Form data.
	 * @param string $key Field name.
	 * 
	 * @return mixed Default value or values as array.
	 */
	public function getDefaultValues($settings, $values, $key) {
		$defaultValues = isset($values[$key]) ? $values[$key] : $settings->options->default;
		if ($defaultValues !== null && !is_array($defaultValues) && $settings->options->multiple)
			$defaultValues = explode(',', $defaultValues);
		else if ($defaultValues === null && $settings->options->multiple)
			$defaultValues = array();
		return $defaultValues;
	}

	/**
	 * Process field attributes from field settings array.
	 * 
	 * @param Array $settings Field settings array.
	 * 
	 * @return Array Field attributes array.
	 */
	public function processFieldAttributes($settings) {
		$attr = array(
			'wrap' => array(
				'attr' => $settings->options->attr ?: array(),'dataAttr' => $settings->options->dataAttr ?: array()
			)
			,'label' => array(
				'attr' => $settings->options->labelAttr ?: array(),'dataAttr' => $settings->options->labelDataAttr ?: array()
			)
			,'labelWrap' => array(
				'attr' => $settings->options->labelWrapAttr ?: array(),'dataAttr' => $settings->options->labelWrapDataAttr ?: array()
			)
			,'input' => array(
				'attr' => $settings->options->inputAttr ?: array(),'dataAttr' => $settings->options->inputDataAttr ?: array()
			)
			,'inputWrap' => array(
				'attr' => $settings->options->inputWrapAttr ?: array(),'dataAttr' => $settings->options->inputWrapDataAttr ?: array()
			)
			,'subInput' => array(
				'attr' => $settings->options->subInputAttr ?: array(),'dataAttr' => $settings->options->subInputDataAttr ?: array()
			)
			,'subInputWrap' => array(
				'attr' => $settings->options->subInputWrapAttr ?: array(),'dataAttr' => $settings->options->subInputWrapDataAttr ?: array()
			)
			,'help' => array(
				'attr' => $settings->options->helpAttr ?: array(),'dataAttr' => $settings->options->helpDataAttr ?: array()
			)
			,'helpWrap' => array(
				'attr' => $settings->options->helpWrapAttr ?: array(),'dataAttr' => $settings->options->helpWrapDataAttr ?: array()
			)
			,'error' => array(
				'attr' => $settings->options->errorAttr ?: array(),'dataAttr' => $settings->options->errorDataAttr ?: array()
			)
			,'errorWrap' => array(
				'attr' => $settings->options->errorWrapAttr ?: array(),'dataAttr' => $settings->options->errorWrapDataAttr ?: array()
			)
			,'data' => array(
				'attr' => $settings->options->dataAttr ?: array(),'dataAttr' => $settings->options->dataDataAttr ?: array()
			)
			,'dataWrap' => array(
				'attr' => $settings->options->dataWrapAttr ?: array(),'dataAttr' => $settings->options->dataWrapDataAttr ?: array()
			)
		);
		return $attr;
	}

	/**
	 * Get field name with a suffix added to a numerical subname. eg. `form[file_1]`
	 * 
	 * @param string $fieldname Field name attribute.
	 * @param string $suffix Sufffix to add.
	 * @param string $glue Optional. Glue to append suffix with.
	 * 
	 * @return string Suffixed name.
	 */
	public function getFieldSuffixNumericId($fieldname, $suffix, $glue = '_') {
		if (preg_match('/^([^\[\]]+)\[([^\[\]]+)\]$/', $fieldname, $match)) {
			$formName = $match[1];
			if (preg_match('/^([a-zA-Z_-]+)([0-9]+)?$/', $match[2], $match)) {
				$newFieldId = "{$match[1]}" . (preg_match('/' . $glue . '$/', $match[1]) ? '' : $glue) . "new_window" . (isset($match[2]) && $match[2] && $match[2] != '' ? "{$glue}{$match[2]}" : '');
				return "{$formName}[{$newFieldId}]";
			}
			else {
				return "{$match[1]}[{$match[2]}{$suffix}]";
			}
		}
		else {
			return $fieldname . $suffix;
		}
	}

	/**
	 * Build field id based of of field name for array type naming convention.
	 * 
	 * @param string $fieldname The field name attribute.
	 * 
	 * @return string Field id.
	 */
	public function getFieldId($fieldname) {
		if (preg_match('/^([^\[\]]+)\[([^\[\]]+)\]$/', $fieldname, $match)) {
			return "{$match[1]}_{$match[2]}";
		}
		else
			return $fieldname;
	}

	/**
	 * Get field name with a suffix added to the sub name.
	 * 
	 * @param string $fieldname Field name attribute.
	 * @param string $suffix Sufffix to add.
	 * 
	 * @return string Suffixed name.
	 */
	public function getFieldSuffixId($fieldname, $suffix) {
		if (preg_match('/^([^\[\]]+)\[([^\[\]]+)\]$/', $fieldname, $match)) {
			return "{$match[1]}[{$match[2]}{$suffix}]";
		}
		else
			return $fieldname . $suffix;
	}

	/**
	 * Adds a class to a specified element class set.
	 * 
	 * @param Array $attr Attribute array sets.
	 * @param string $key Input part/section.
	 * @param string $class Class name.
	 * @param string $type Optional. Attribute type. 'attr' or 'dataAttr'
	 */
	public function addAttrClass(&$attr, $key, $class, $type = 'attr') {
		if (isset($attr[$key][$type])) {
			if (!isset($attr[$key][$type]['class']))
				$attr[$key][$type]['class'] = '';
			$class = $attr[$key][$type]['class'] == '' ? $class : " {$class}";
			$attr[$key][$type]['class'] .= $class;
		}
	}

	public function convertToDateTime($value, $format = null) {
		$dt = null;
		if ($value) {
			if ($format) {
				$dt = DateTime::createFromFormat($format, $value);
			}
			else {
				$formats = [
					'/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9])$/' => 'Y-m-d'
					,'/^(?:[0-2][0-9]):(?:[0-5][0-9])$/' => 'H:i'
					,'/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9]) (?:[0-2][0-9]):(?:[0-5][0-9])$/' => 'Y-m-d H:i'
					,'/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9]) (?:[0-1]?[0-9]):(?:[0-5][0-9]) (am|pm|AM|PM)$/' => 'Y-m-d g:i a'
					,'/^\d{14}$/' => 'YmdHis'
					,'/^\d{12}$/' => 'YmdHi'
					,'/^\d{8}$/' => 'Ymd'
					,'/^\d{4}$/' => 'Hi'
				];
				foreach ($formats as $regex => $format) {
					if (preg_match($regex, $value)) {
						try {
							$dt = DateTime::createFromFormat($format, strtolower($value));
							break;
						}
						catch(Exception $e) {
							$dt = null;
						}
					}
				}
				if (!$dt) {
					try {
						$dt = new DateTime($value);
					}
					catch (Exception $e) {
						$dt = null;
					}
				}
			}
		}
		return $dt;
	}
}