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
 * Color input type.
 */
class ColorInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'colorField';

	/**
	 * Validate submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Boolean as first value that indicates whether or not the value was valid.
	 *               Second ootional value describes the error.
	 */
	function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == ''))
			return [false, "{$displayName} is required"];
		else if (isset($data[$this->name]) && $data[$this->name] && !$this->validator->hexColor($data[$this->name]))
			return [false, "{$displayName} must be a valid hex color value"];
		else
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

		//--name, type and value
		$attr['input']['attr']['name'] = $this->inputName;
		$attr['input']['attr']['type'] = 'color';
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
		if ($this->settings->options->placeholder)
			$attr['input']['attr']['placeholder'] = $this->settings->options->placeholder;

		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');

		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}