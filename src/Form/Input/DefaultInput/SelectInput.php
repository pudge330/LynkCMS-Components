<?php
namespace BGStudios\Component\Form\Input\DefaultInput;

use BGStudios\Component\Form\Input\InputType;

class SelectInput extends CheckboxInput {
	protected $fieldName = 'selectField';

	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);

		//--name, type, value(s)
		$attr['input']['attr']['name'] = $this->inputName;
		$submittedValues = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		if ($submittedValues === null)
			$submittedValues = [];
		else if (!is_array($submittedValues))
			$submittedValues = [$submittedValues];

		//--id, class
		$fieldId = $this->helper->getFieldId($this->inputName);
		$attr['input']['attr']['id'] = $fieldId;
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $fieldId);
		$this->helper->addAttrClass($attr, 'input', $classes['input']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		if ($this->settings->options->multiple) {
			$attr['input']['attr']['name'] .= '[]';
			$attr['input']['attr']['multiple'] = 'multiple';
			$this->helper->addAttrClass($attr, 'wrap', 'multi' . ucfirst($this->fieldName));
		}
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		if ($this->settings->options->resiable)
			$attr['input']['dataAttr']['resiable'] = 'true';

		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');

		$output = '';
		$count = -1;
		foreach ($this->settings->options->data as $dataKey => $dataValue) {
			$count++;
			$selected = in_array($dataKey, $submittedValues) ? ' selected="selected"' : '';
			$output .= "<option value=\"{$dataKey}\" id=\"{$fieldId}_{$count}\"{$selected}>{$dataValue}</option>";
		}
		return "<select{$inputAttr}{$inputDataAttr}>{$output}</select>";
	}
}