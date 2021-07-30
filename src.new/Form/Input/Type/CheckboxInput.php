<?php
/**
 * This file is part of the LynkCMS Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS Components
 * @subpackage Form
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Form\Input\Type;

use LynkCMS\Component\Form\Input\InputType;

/**
 * Checkbox input type.
 */
class CheckboxInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'checkboxField';

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		if ($settings->options->data && !is_array($settings->options->data))
			$settings->options->data = $this->helper->processCsv($settings->options->data, $settings->options->dataDormat);
		else if (!$settings->options->data)
			$settings->options->data = [];
		if ($settings->options->dataFile) {
			$settings->options->data = $this->helper->processDataFile($settings->options->dataFile, $settings->options->dataFileFormat) + $settings->options->data;
		}
		if (!$settings->options->layout)
			$settings->options->layout = 'inline';
		return $settings;
	}

	/**
	 * Process submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Data values.
	 */
	public function processData($data) {
		if (!isset($data[$this->name]) || !$data[$this->name])
			$data[$this->name] = null;
		return $data;
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
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == '' || (is_array($data[$this->name]) && sizeof($data[$this->name]) == 0))) {
			return [false, "{$displayName} is required"];
		}
		else if (isset($data[$this->name]) && $data[$this->name]) {
			if (is_array($data[$this->name])) {
				for ($i = 0; $i < sizeof($data[$this->name]); $i++) {
					if (!$this->helper->validateType($this->settings->options->allow, $data[$this->name][$i]))
						return [false, "{$displayName} must be one or more {$this->settings->options->allow} values"];
					// if (sizeof($this->settings->options->data) > 0 && !$this->helper->validateExists($data[$this->name][$i], $this->settings->options->data))
					// 	return [false, "{$displayName} contains invalid data"];
				}
			}
			else {
				if (!$this->helper->validateType($this->settings->options->allow, $data[$this->name]))
					return [false, "{$displayName} must be a {$this->settings->options->allow} value"];
				// if (sizeof($this->settings->options->data) > 0 && !$this->helper->validateExists($data[$this->name], $this->settings->options->data))
				// 	return [false, "{$displayName} contains invalid data"];
			}
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
		//--name, type, value(s)
		$attr['input']['attr']['name'] = $this->inputName;
		$attr['input']['attr']['type'] = 'checkbox';
		$submittedValues = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		if ($submittedValues === null)
			$submittedValues = [];
		else if (!is_array($submittedValues))
			$submittedValues = [$submittedValues];

		//--id, class
		$fieldId = $this->helper->getFieldId($this->inputName);
		if (!isset($attr['input']['attr']['class']))
			$attr['input']['attr']['class'] = '';
		$attr['input']['attr']['class'] .= $this->settings->options->class ? " {$this->settings->options->class}" : '';
		$attr['input']['attr']['class'] .= " {$fieldId}";
		$attr['input']['attr']['class'] .= " {$classes['subinput']}";
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->layout) {
			$attr['wrap']['dataAttr']['layout'] = $this->settings->options->layout;
		}

		if (sizeof($this->settings->options->data) > 1)
			$this->settings->options->multiple = true;

		if ($this->settings->options->multiple)
			$attr['input']['attr']['name'] .= '[]';

		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$output = '';
		$count = -1;
		foreach ($this->settings->options->data as $dataKey => $dataValue) {
			$count++;
			$selected = in_array($dataKey, $submittedValues) ? ' checked="checked"' : '';
			$attr['input']['attr']['value'] = htmlentities($dataKey);
			$attr['input']['attr']['id'] = $fieldId . "_{$count}";
			$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
			$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr']);
			$output .= "<label for=\"{$attr['input']['attr']['id']}\" class=\"{$classes['sublabel']}\"><input{$inputAttr}{$inputDataAttr}{$selected}><span>{$dataValue}</span></label>";
		}

		return $output;
	}
}