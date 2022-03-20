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
 * Range input type.
 */
class RangeInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'rangeField';

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		if ($settings->options->step === null)
			$settings->options->step = '1';
		if ($settings->options->min === null)
			$settings->options->min = '1';
		if ($settings->options->max === null)
			$settings->options->max = '100';
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
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == ''))
			return [false, "{$displayName} is required"];
		else if (isset($data[$this->name]) && $data[$this->name] && !$this->validator->int($data[$this->name], ['min' => $this->settings->options->min, 'max' => $this->settings->options->max])) {
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

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');
		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}