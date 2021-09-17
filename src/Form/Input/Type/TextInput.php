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
 * Text input type.
 */
class TextInput extends InputType {

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
		if ($this->settings->options->required && (!isset($data[$this->name]) || ($data[$this->name] === null || $data[$this->name] == '')))
			return [false, "{$displayName} is required"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->min && $this->settings->options->max && !$this->validator->text($data[$this->name], $this->settings->options->max, $this->settings->options->min))
			return [false, "{$displayName} must be between {$this->settings->options->min} and {$this->settings->options->max} characters"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->max && !$this->validator->text($data[$this->name], $this->settings->options->max))
			return [false, "{$displayName} must be {$this->settings->options->max} characters or less"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->min && strlen($data[$this->name]) < $this->settings->options->min)
			return [false, "{$displayName} must be {$this->settings->options->min} characters or more"];
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
		$attr['input']['attr']['type'] = $this->settings->options->inputType ?: 'text';
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
		if ($this->settings->options->readonly)
			$attr['input']['attr']['readonly'] = 'readonly';
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';
		if ($this->settings->options->max)
			$attr['input']['attr']['maxlength'] = $this->settings->options->max;
		if ($this->settings->options->placeholder)
			$attr['input']['attr']['placeholder'] = $this->settings->options->placeholder;

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');
		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}