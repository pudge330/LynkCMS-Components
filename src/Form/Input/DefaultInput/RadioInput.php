<?php
namespace BGStudios\Component\Form\Input\DefaultInput;

use BGStudios\Component\Form\Input\InputType;

class RadioInput extends InputType {
	protected $fieldName = 'radioField';
	public function processSettings($settings) {
		if ($settings->options->data && !is_array($settings->options->data))
			$settings->options->data = $this->helper->processCsv($settings->options->data, $settings->options->format);
		else if (!$settings->options->data)
			$settings->options->data = [];
		if ($settings->options->dataFile) {
			$settings->options->data = array_merge(
				$settings->options->data
				,$this->helper->processDataFile($settings->options->dataFile, $settings->options->format)
			);
		}
		return $settings;
	}
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == '')) {
			return [false, "{$displayName} is required"];
		}
		else if (isset($data[$this->name]) && $data[$this->name]) {
			if (!$this->helper->validateType($this->settings->options->allow, $data[$this->name]))
				return [false, "{$displayName} must be a {$this->settings->options->allow} value"];
			// if (sizeof($this->settings->options->data) > 0 && !$this->helper->validateExists($data[$this->name], $this->settings->options->data))
			// 	return [false, "{$displayName} contains invalid data"];
		}
		return [true];
	}
	public function renderLabel(&$attr, $values = [], &$errors = []) {
		return $this->settings->label && !$this->settings->options->noLabel ? $this->settings->label : null;
	}
	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);

		//--name, type and value
		$attr['input']['attr']['name'] = $this->inputName;
		$attr['input']['attr']['type'] = 'radio';
		$submittedValues = $this->helper->getDefaultValues($this->settings, $values, $this->name);

		//--id, class
		$fieldId = $this->helper->getFieldId($this->inputName);
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $fieldId);
		$this->helper->addAttrClass($attr, 'input', $classes['subinput']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		if ($this->settings->options->max)
			$attr['input']['attr']['maxlength'] = $this->settings->options->max;
		if ($this->settings->options->placeholder)
			$attr['input']['attr']['placeholder'] = $this->settings->options->placeholder;
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$output = '';
		$count = -1;
		foreach ($this->settings->options->data as $dataKey => $dataValue) {
			$count++;
			$selected = $dataKey == $submittedValues ? ' checked="checked"' : '';
			$attr['input']['attr']['value'] = htmlentities($dataKey);
			$attr['input']['attr']['id'] = $fieldId . "_{$count}";
			$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
			$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr']);
			$output .= "<label for=\"{$attr['input']['attr']['id']}\" class=\"{$classes['sublabel']}\"><input{$inputAttr}{$inputDataAttr}{$selected}><span>{$dataValue}</span></label>";
		}

		return $output;
	}
}