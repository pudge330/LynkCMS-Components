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
 * Selectable Time input type.
 */
class SelectableTimeInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'selectableTimeField';

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
		$min = $min ? Datetime::createFromFormat('Y-m-d H:i', '1970-01-01 ' . $min) : null;
		$max = $max ? Datetime::createFromFormat('Y-m-d H:i', '1970-01-01 ' . $max) : null;

		$hourValue = isset($data["{$this->name}_hour"]) && $data["{$this->name}_hour"] != '' ? $data["{$this->name}_hour"] : null;
		$minuteValue = isset($data["{$this->name}_minute"]) && $data["{$this->name}_minute"] != '' ? $data["{$this->name}_minute"] : null;
		$periodValue = isset($data["{$this->name}_period"]) && $data["{$this->name}_period"] != '' ? $data["{$this->name}_period"] : null;
		$hourInRange = $hourValue && ((int)$hourValue >= 0 && (int)$hourValue <= 12);
		$minuteInRange = $minuteValue && ((int)$minuteValue >= 0 && (int)$minuteValue <= 60);
		$periodInRange = $periodValue && ($periodValue == 'AM' || 'PM');

		$allExists = ($hourValue && $minuteValue && $periodValue);
		$isAllValid = ($hourInRange && $minuteInRange && $periodInRange);

		if ($this->settings->options->required && !$allExists) {
			return [false, "{$displayName} is required"];
		}
		else if ($allExists && !$isAllValid) {
			return [false, "{$displayName} had invalid options"];
		}
		else if ($isAllValid) {
			$submittedObject = Datetime::createFromFormat('Y-m-d h:i A', "1970-01-01 {$hourValue}:{$minuteValue} {$periodValue}");
			$passedMinTest = $passedMaxTest = false;
			if ($min)
				$passedMinTest = ((int)$submittedObject->format('Hi') >= (int)$min->format('Hi'));
			if ($max)
				$passedMaxTest = ((int)$submittedObject->format('Hi') <= (int)$max->format('Hi'));
			$formattedMin = $min ? $min->format('h:i A') : '';
			$formattedMax = $max ? $max->format('h:i A') : '';
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
		$submittedValues = array('year' => null, 'month' => null, 'day' => null, 'hour' => null, 'minute' => null, 'second' => null, 'period' => null);
		$submittedValue = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		if ($submittedValue) {
			$submittedDatetime = Datetime::createFromFormat('Y-m-d H:i', date('Y-m-d') . ' ' . $submittedValue);
			$submittedValues['hour'] = $submittedDatetime->format('H');
			$submittedValues['minute'] = $submittedDatetime->format('i');
			$submittedValues['period'] = $submittedDatetime->format('A');
		}
		$submittedValues['hour'] = isset($values["{$this->name}_hour"]) ? $values["{$this->name}_hour"] : $submittedValues['hour'];
		$submittedValues['minute'] = isset($values["{$this->name}_minute"]) ? $values["{$this->name}_minute"] : $submittedValues['minute'];
		$submittedValues['period'] = isset($values["{$this->name}_period"]) ? $values["{$this->name}_period"] : $submittedValues['period'];

		//--id, class
		$fieldId = $this->helper->getFieldId($this->inputName);
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $fieldId);
		$this->helper->addAttrClass($attr, 'input', $classes['subinput']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$inputClasses = $attr['input']['attr']['class'];

		//--date inputs
		$dateOutput = '';

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_hour');
		$attr['input']['attr']['id'] = "{$fieldId}_hour";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-hour";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"\" id=\"{$fieldId}_none\">--</option>";
		for ($i = 1; $i <= 12; $i++) {
			$selected = sprintf("%02d", $i) == $submittedValues['hour'] ? ' selected="selected"' : '';
			$dateOutput .= "<option value=\"".sprintf("%02d", $i)."\" id=\"{$fieldId}_".sprintf("%02d", $i)."\"{$selected}>".sprintf("%02d", $i)."</option>";
		}
		$dateOutput .= "</select>";

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_minute');
		$attr['input']['attr']['id'] = "{$fieldId}_minute";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-minute";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"\" id=\"{$fieldId}_none\">--</option>";
		for ($i = 0; $i <= 59; $i++) {
			$selected = sprintf("%02d", $i) == $submittedValues['minute'] ? ' selected="selected"' : '';
			$dateOutput .= "<option value=\"".sprintf("%02d", $i)."\" id=\"{$fieldId}_".sprintf("%02d", $i)."\"{$selected}>".sprintf("%02d", $i)."</option>";
		}
		$dateOutput .= "</select>";

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_period');
		$attr['input']['attr']['id'] = "{$fieldId}_period";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-period";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$amSelected = 'AM' == $submittedValues['period'] ? ' selected="selected"' : '';
		$pmSelected = 'PM' == $submittedValues['period'] ? ' selected="selected"' : '';
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"AM\" id=\"{$fieldId}_am\"{$amSelected}>AM</option>";
		$dateOutput .= "<option value=\"PM\" id=\"{$fieldId}_pm\"{$pmSelected}>PM</option>";
		$dateOutput .= "</select>";

		return "<div class=\"subInputWrap\">{$dateOutput}</div>";
	}
}