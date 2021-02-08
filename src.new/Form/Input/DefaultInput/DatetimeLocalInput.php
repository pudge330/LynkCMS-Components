<?php
namespace LynkCMS\Component\Form\Input\DefaultInput;

use Datetime;
use LynkCMS\Component\Form\Input\InputType;

class DatetimeLocalInput extends InputType {
	protected $fieldName = 'datetimeLocalField';
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		
		$min = $this->settings->options->min;
		$max = $this->settings->options->max;
		$min = $min ? Datetime::createFromFormat('Y-m-d H:i', $min) : null;
		$max = $max ? Datetime::createFromFormat('Y-m-d H:i', $max) : null;

		$isSubmitted = (isset($data[$this->name]) && $data[$this->name] && $data[$this->name] != '');

		if ($this->settings->options->required && !$isSubmitted) {
			return [false, "{$displayName} is required"];
		}
		else if ($isSubmitted) {
			$submittedObject = new Datetime($data[$this->name]);
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
	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);

		//--name, type, step and value
		$attr['input']['attr']['name'] = $this->inputName;
		$attr['input']['attr']['type'] = 'datetime-local';
		$attr['input']['attr']['value'] = $this->helper->getDefaultValues($this->settings, $values, $this->name);

		//--id, class
		$attr['input']['attr']['id'] = $this->helper->getFieldId($this->inputName);
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

		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');

		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}