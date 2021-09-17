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

use Datetime;
use LynkCMS\Component\Form\Input\InputType;

/**
 * Date input type.
 */
class DateInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'dateField';

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
		
		$min = $this->settings->options->min;
		$max = $this->settings->options->max;
		$min = $min ? Datetime::createFromFormat('Y-m-d H:i', $min . ' 00:00') : null;
		$max = $max ? Datetime::createFromFormat('Y-m-d H:i', $max . ' 00:00') : null;

		$isSubmitted = (isset($data[$this->name]) && $data[$this->name] && $data[$this->name] != '');

		if ($this->settings->options->required && !$isSubmitted) {
			return [false, "{$displayName} is required"];
		}
		else if ($isSubmitted) {
			$submittedObject = new Datetime($data[$this->name]);
			if ($submittedObject) {
				$passedMinTest = $passedMaxTest = false;
				if ($min)
					$passedMinTest = ((int)$submittedObject->format('Ymd') >= (int)$min->format('Ymd'));
				if ($max)
					$passedMaxTest = ((int)$submittedObject->format('Ymd') <= (int)$max->format('Ymd'));
				$formattedMin = $min ? $min->format('Y-m-d') : '';
				$formattedMax = $max ? $max->format('Y-m-d') : '';
				if ($min && $max && (!$passedMinTest || !$passedMaxTest)) {
					return [false, "{$displayName} must be on or between {$formattedMin} and {$formattedMax}"];
				}
				else if ($min && !$passedMinTest) {
					return [false, "{$displayName} must be on or after {$formattedMin}"];
				}
				else if ($max && !$passedMaxTest) {
					return [false, "{$displayName} must be on or before {$formattedMax}"];
				}
			}
			else {
				return [false, "{$displayName} is an invalid format"];
			}
		}
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
		$attr['input']['attr']['type'] = 'date';
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
		if ($this->settings->options->min)
			$attr['input']['attr']['min'] = $this->settings->options->min;
		if ($this->settings->options->max)
			$attr['input']['attr']['max'] = $this->settings->options->max;
		if ($this->settings->options->placeholder)
			$attr['input']['attr']['placeholder'] = $this->settings->options->placeholder;
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');

		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}