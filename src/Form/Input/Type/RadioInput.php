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

use Lynk\Component\Form\Input\InputType;

/**
 * Radio input type.
 */
class RadioInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'radioField';

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
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

	/**
	 * Render input label.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered label.
	 */
	public function renderLabel(&$attr, $values = [], &$errors = []) {
		return $this->settings->label && !$this->settings->options->noLabel ? $this->settings->label : null;
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
			$inputAttr = \lynk\attributes($attr['input']['attr']);
			$inputDataAttr = \lynk\attributes($attr['input']['dataAttr']);
			$output .= "<label for=\"{$attr['input']['attr']['id']}\" class=\"{$classes['sublabel']}\"><input{$inputAttr}{$inputDataAttr}{$selected}><span>{$dataValue}</span></label>";
		}

		return $output;
	}
}