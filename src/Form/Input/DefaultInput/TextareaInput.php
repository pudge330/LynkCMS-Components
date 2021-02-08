<?php
namespace BGStudios\Component\Form\Input\DefaultInput;

use BGStudios\Component\Form\Input\InputType;

class TextareaInput extends InputType {
	protected $fieldName = 'textareaField';
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		if ($this->settings->options->required && (!isset($data[$this->name]) || ($data[$this->name] === null || $data[$this->name] == '')))
			return [false, "{$displayName} is required"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->min && $this->settings->options->max && !$this->val->text($data[$this->name], $this->settings->options->max, $this->settings->options->min))
			return [false, "{$displayName} must be between {$this->settings->options->min} and {$this->settings->options->max} characters"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->max && !$this->val->text($data[$this->name], $this->settings->options->max))
			return [false, "{$displayName} must be {$this->settings->options->max} characters or less"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->min && strlen($data[$this->name]) < $this->settings->options->min)
			return [false, "{$displayName} must be {$this->settings->options->min} characters or more"];
		else
			return [true];
	}
	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);

		//--name, type and value
		$attr['input']['attr']['name'] = $this->inputName;
		$submittedValue = $this->helper->getDefaultValues($this->settings, $values, $this->name);

		//--id, class
		$attr['input']['attr']['id'] = $this->helper->getFieldId($this->inputName);
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $attr['input']['attr']['id']);
		$this->helper->addAttrClass($attr, 'input', $classes['input']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		if ($this->settings->options->singleField)
			$this->helper->addAttrClass($attr, 'wrap', "singleField");
		if ($this->settings->options->verticalField)
			$this->helper->addAttrClass($attr, 'wrap', "verticalField");
		if ($this->settings->options->max)
			$attr['input']['attr']['maxlength'] = $this->settings->options->max;
		if ($this->settings->options->readonly)
			$attr['input']['attr']['readonly'] = 'readonly';
		if ($this->settings->options->placeholder)
			$attr['input']['attr']['placeholder'] = $this->settings->options->placeholder;
		if ($this->settings->options->readonly)
			$attr['input']['attr']['readonly'] = 'readonly';
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		return "<textarea{$inputAttr}{$inputDataAttr}>{$submittedValue}</textarea>";
	}
}