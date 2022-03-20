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
 * Integer input type.
 */
class IntegerInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'integerField';

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
		else if (isset($data[$this->name]) && $data[$this->name] && !$this->validator->int($data[$this->name], ['min' => $this->settings->options->min, 'max' => $this->settings->options->max])) {
			if ($this->settings->options->min && $this->settings->options->max)
				return [false, "{$displayName} must be a integer between {$this->settings->options->min} and {$this->settings->options->max}"];
			else if ($this->settings->options->min)
				return [false, "{$displayName} must be a integer at least {$this->settings->options->min} or greater"];
			else if ($this->settings->options->max)
				return [false, "{$displayName} must be a integer no greater than {$this->settings->options->max}"];
			else
				return [false, "{$displayName} must be a integer"];
		}
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

		//--name, type, step and value
		$attr['input']['attr']['name'] = $this->inputName;
		$attr['input']['attr']['type'] = 'number';
		$attr['input']['attr']['value'] = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		$attr['input']['attr']['step'] = '1';

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
		if ($this->settings->options->readonly)
			$attr['input']['attr']['readonly'] = 'readonly';
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');

		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}