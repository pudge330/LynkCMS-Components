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

namespace Lynk\Component\Form\Input\Type;

use Datetime;
use Lynk\Component\Form\Input\InputType;

/**
 * Date and Time input type.
 */
class DatetimeInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'datetimeField';

	/**
	 * Validate submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Boolean as first value that indicates whether or not the value was valid.
	 *               Second ootional value describes the error.
	 */
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		
		$min = $this->settings->options->min;
		$max = $this->settings->options->max;
		$min = $min ? Datetime::createFromFormat('Y-m-d H:i', $min) : null;
		$max = $max ? Datetime::createFromFormat('Y-m-d H:i', $max) : null;

		$submittedDate = isset($data["{$this->name}_date"]) && $data["{$this->name}_date"] != '' ? $data["{$this->name}_date"] : null;
		$submittedTime = isset($data["{$this->name}_time"]) && $data["{$this->name}_time"] != '' ? $data["{$this->name}_time"] : null;
		$isSubmitted = ($submittedDate && $submittedTime);

		if ($this->settings->options->required && !$isSubmitted) {
			return [false, "{$displayName} is required"];
		}
		else if ($isSubmitted) {
			$submittedObject = Datetime::createFromFormat('Y-m-d H:i', "{$submittedDate} {$submittedTime}");
			if ($submittedObject) {
				$passedMinTest = $passedMaxTest = false;
				if ($min)
					$passedMinTest = ((int)$submittedObject->format('YmdHi') >= (int)$min->format('YmdHi'));
				if ($max)
					$passedMaxTest = ((int)$submittedObject->format('YmdHi') <= (int)$max->format('YmdHi'));
				$formattedMin = $min ? $min->format('Y-m-d H:i') : '';
				$formattedMax = $max ? $max->format('Y-m-d H:i') : '';
				if ($min && $max && (!$passedMinTest || !$passedMaxTest)) {
					return [false, "{$displayName} must be on or between {$formattedMin} and {$formattedMax}"];
				}
				else if ($min && !$passedMinTest) {
					return [false, "{$displayName} must be on or after {$formattedMin}"];
				}
				else if ($max && !$passedMaxTest) {
					return [false, "{$displayName} must be on or before {$formattedMax}"];
				}
			}
			else {
				return [false, "{$displayName} is an invalid format"];
			}
		}
		return [true];
	}

	/**
	 * Render input.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered input.
	 */
	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);

		//--name, type, step and value
		// $attr['input']['attr']['name'] = $this->inputName;
		// $attr['input']['attr']['type'] = 'datetime-local';

		$submittedDate = $submittedTime = null;
		$submittedValue = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		if ($submittedValue) {
			$submittedDatetime = $this->helper->convertToDateTime($submittedValue);
			$submittedDate = $submittedDatetime->format('Y-m-d');
			$submittedTime = $submittedDatetime->format('H:i');
		}
		$submittedDate = isset($values["{$this->name}_date"]) ? $values["{$this->name}_date"] : $submittedDate;
		$submittedTime = isset($values["{$this->name}_time"]) ? $values["{$this->name}_time"] : $submittedTime;

		//--id, class
		$fieldId = $attr['input']['attr']['id'] = $this->helper->getFieldId($this->inputName);
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $attr['input']['attr']['id']);
		$this->helper->addAttrClass($attr, 'input', $classes['input']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		if ($this->settings->options->min)
			$attr['input']['attr']['min'] = $this->settings->options->min;
		if ($this->settings->options->max)
			$attr['input']['attr']['max'] = $this->settings->options->max;
		if ($this->settings->options->placeholder)
			$attr['input']['attr']['placeholder'] = $this->settings->options->placeholder;
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$classes = $attr['input']['attr']['class'];

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_date');
		$attr['input']['attr']['type'] = 'date';
		$attr['input']['attr']['id'] = "{$fieldId}_date";
		$attr['input']['attr']['class'] = "{$classes} {$fieldId} {$fieldId}_date";
		if ($submittedDate)
			$attr['input']['attr']['value'] = $submittedDate;
		$attr['input']['dataAttr']['dataid'] = "{$fieldId}_time";
		$dateInputAttr = \lynk\attributes($attr['input']['attr']);
		$dateInputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');
		unset($attr['input']['attr']['value']);
		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_time');
		$attr['input']['attr']['type'] = 'time';
		$attr['input']['attr']['id'] = "{$fieldId}_time";
		$attr['input']['attr']['class'] = "{$classes} {$fieldId} {$fieldId}_time";
		if ($submittedTime)
			$attr['input']['attr']['value'] = $submittedTime;
		$timeInputAttr = \lynk\attributes($attr['input']['attr']);
		$timeInputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');
		return "<div><input{$dateInputAttr}{$dateInputDataAttr}></div><div><input{$timeInputAttr}{$timeInputDataAttr}></div>";
	}
}