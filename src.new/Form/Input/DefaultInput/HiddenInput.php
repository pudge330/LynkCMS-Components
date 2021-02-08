<?php
namespace LynkCMS\Component\Form\Input\DefaultInput;

use LynkCMS\Component\Form\Input\InputType;

class HiddenInput extends InputType {
	protected $fieldName = 'hiddenField';
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == ''))
			return [false, "{$displayName} is missing"];
		else
			return [true];
	}
	public function outputHtml($values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);
		$attr = $this->helper->processFieldAttributes($this->settings);

		$this->helper->addAttrClass($attr, 'input', $this->fieldName);

		//--name, type and value
		$attr['input']['attr']['name'] = $this->inputName;
		$attr['input']['attr']['type'] = 'hidden';
		$attr['input']['attr']['value'] = htmlentities($this->helper->getDefaultValues($this->settings, $values, $this->name));

		//--id, class
		$attr['input']['attr']['id'] = $this->helper->getFieldId($this->inputName);
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $attr['input']['attr']['id']);
		$this->helper->addAttrClass($attr, 'input', $classes['input']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');

		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}