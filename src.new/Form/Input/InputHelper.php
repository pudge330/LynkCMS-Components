<?php

/*
 * This file is part of LynkCMS Form Component.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LynkCMS\Component\Form\Input;

use DateTime;
use LynkCMS\Component\Form\Validator\BasicDataValidator;

/**
 * Form input helper
 *
 * Useful functions for input processing/rendering
 *
 * @author Brandon Garcia <me@bgarcia.dev>
 */
class InputHelper {
	public $val;
	public $buffer;

	public function __construct(BasicDataValidator $validator = null) {
		$validator = $validator ?: new BasicDataValidator();
		$this->val = $validator;
		$this->buffer = new InputNamedBuffers();
	}
	public function toDateTime($value) {
		if (!$value)
			return null;
		if (is_numeric($value)) {
			if (preg_match('/^\d{8}$/', $value)) {
				return DateTime::createFromFormat('Ymd', $value);
			}
			else if (preg_match('/^\d{14}$/', $value)) {
				return DateTime::createFromFormat('YmdHis', $value);
			}
			else {
				return DateTime::createFromFormat('YmdHi', $value);
			}
		}
		else {
			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
				return Datetime::createFromFormat('Y-m-d H:i', $submittedValue . ' ' . date('H:i'));
			}
			else {
				return new Datetime($value);
			}
		}
	}
	public function addAttrClass(&$attr, $key, $class, $type = 'attr') {
		if (isset($attr[$key][$type])) {
			if (!isset($attr[$key][$type]['class']))
				$attr[$key][$type]['class'] = '';
			$class = $attr[$key][$type]['class'] == '' ? $class : " {$class}";
			$attr[$key][$type]['class'] .= $class;
		}
	}
	public function getFieldId($fieldname) {
		if (preg_match('/^([^\[\]]+)\[([^\[\]]+)\]$/', $fieldname, $match)) {
			return "{$match[1]}_{$match[2]}";
		}
		else
			return $fieldname;
	}
	public function getFieldSuffixId($fieldname, $suffix) {
		if (preg_match('/^([^\[\]]+)\[([^\[\]]+)\]$/', $fieldname, $match)) {
			return "{$match[1]}[{$match[2]}{$suffix}]";
		}
		else
			return $fieldname . $suffix;
	}
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
	public function getDefaultValues($settings, $values, $key) {
		$defaultValues = $settings->options->default;
		if ($defaultValues !== null && !is_array($defaultValues) && $settings->options->multiple)
			$defaultValues = explode(',', $defaultValues);
		else if ($defaultValues === null && $settings->options->multiple)
			$defaultValues = array();
		if (isset($values[$key])) {
			$defaultValues = $values[$key];
			if ($defaultValues !== null && !is_array($defaultValues) && $settings->options->multiple)
				$defaultValues = explode(',', $defaultValues);
			else if ($defaultValues === null && $settings->options->multiple)
				$defaultValues = array();
		}
		return $defaultValues;
	}
	public function formatFileSize($filesize, array $labels = array()) {
		$labels = array_merge(array(
			'b' => 'b'
			,'kb' => 'kb'
			,'mb' => 'mb'
			,'gb' => 'gb'
			,'tb' => 'tb'
		), $labels);
		if ($filesize < 1024)
			$filesize = round($filesize, 2) . $labels['b'];
		else if ($filesize >= 1024 && $filesize < (1024 * 1024))
			$filesize = round($filesize / 1024, 2) . $labels['kb'];
		else if ($filesize >= (1024 * 1024) && $filesize < (1024 * 1024 * 1024))
			$filesize = round($filesize / 1024 / 1024, 2) . $labels['mb'];
		else if ($filesize >= (1024 * 1024 * 1024) && $filesize < (1024 * 1024 * 1024 * 1024))
			$filesize = round($filesize / 1024 / 1024 / 1024, 2) . $labels['gb'];
		else
			$filesize = round($filesize / 1024 / 1024 / 1024  /1024, 2) . $labels['tb'];
		return $filesize;
	}
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
	public function buildAttributeString($data, $prefix = null, $padding = true) {
		$attrString = '';
		foreach ($data as $k => $v)
			$attrString .= $v !== null ? " {$prefix}{$k}=\"{$v}\"" : " {$prefix}{$k}";
		if (!$padding && $attrString != '')
			$attrString = trim($attrString);
		return $attrString;
	}
	public function validateType($allow, $data) {
		if ($allow && in_array($allow, ['email', 'phone', 'text', 'int', 'double', 'rgb', 'hexcolor'])) {
			return $this->val->{$allow}($data);
		}
		return true;
	}
	public function validateExists($data, $source) {
		$source = array_keys($source);
		if (is_array($data)) {
			foreach ($data as $d) {
				if (!in_array($d, $source))
					return false;
			}
		}
		else {
			if (!in_array($data, $source))
				return false;
		}
		return true;
	}
}