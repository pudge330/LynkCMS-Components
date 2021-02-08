<?php
namespace BGStudios\Component\Form\Input\DefaultInput;

use BGStudios\Component\Form\Input\InputType;

class RangeInput extends InputType {
	protected $fieldName = 'rangeField';
	public function processSettings($settings) {
		if ($settings->options->step === null)
			$settings->options->step = '1';
		if ($settings->options->min === null)
			$settings->options->min = '1';
		if ($settings->options->max === null)
			$settings->options->max = '100';
		return $settings;
	}
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == ''))
			return [false, "{$displayName} is required"];
		else if (isset($data[$this->name]) && $data[$this->name] && !$this->val->int($data[$this->name], ['min' => $this->settings->options->min, 'max' => $this->settings->options->max])) {
			if ($this->settings->options->min && $this->settings->options->max)
				return [false, "{$displayName} must be a number between {$this->settings->options->min} and {$this->settings->options->max}"];
			else if ($this->settings->options->min)
				return [false, "{$displayName} must be a number at least {$this->settings->options->min} or greater"];
			else if ($this->settings->options->max)
				return [false, "{$displayName} must be a number no greater than {$this->settings->options->max}"];
			else
				return [false, "{$displayName} must be a number"];
		}
		else
			return [true];
	}
	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);

		//--name, type and value
		$attr['input']['attr']['name'] = $this->inputName;
		$attr['input']['attr']['type'] = 'range';
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
		$attr['input']['attr']['min'] = $this->settings->options->min;
		$attr['input']['attr']['max'] = $this->settings->options->max;
		$attr['input']['attr']['step'] = $this->settings->options->step;
		if ($this->settings->options->placeholder)
			$attr['input']['attr']['placeholder'] = $this->settings->options->placeholder;
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}