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

namespace Lynk\Component\FormNew2\Input\Type\View;

use Lynk\Component\FormNew2\Input\InputView;

/**
 * Double view class.
 */
class SelectableTimeView extends InputView {

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
		$name = $this->input->getName();
		$fieldName = $this->input->getFieldName();
		$inputName = $this->input->getInputName();
		$settings = $this->input->getSettings();
		$helper = $this->input->getHelper();

		// build attributes
		$labelAttr = \lynk\attributes($attr['label']['attr']);
		$labelAttr .= \lynk\attributes($attr['label']['dataAttr'], 'data-');

		// label text
		$labelText = $settings->label && !$settings->options->noLabel
			? $settings->label
			: null;

		return $labelText ? "<label{$labelAttr}>{$labelText}</label>" : null;
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
		$name = $this->input->getName();
		$fieldName = $this->input->getFieldName();
		$inputName = $this->input->getInputName();
		$settings = $this->input->getSettings();
		$helper = $this->input->getHelper();
		$classes = $this->getFieldClasses();

		//--name, type, value(s)
		$submittedValues = array('year' => null, 'month' => null, 'day' => null, 'hour' => null, 'minute' => null, 'second' => null, 'period' => null);
		$submittedValue = $helper->getDefaultValues($settings, $values, $name);
		if ($submittedValue) {
			$submittedDatetime = Datetime::createFromFormat('Y-m-d H:i', date('Y-m-d') . ' ' . $submittedValue);
			$submittedValues['hour'] = $submittedDatetime->format('H');
			$submittedValues['minute'] = $submittedDatetime->format('i');
			$submittedValues['period'] = $submittedDatetime->format('A');
		}
		$submittedValues['hour'] = isset($values["{$name}_hour"]) ? $values["{$name}_hour"] : $submittedValues['hour'];
		$submittedValues['minute'] = isset($values["{$name}_minute"]) ? $values["{$name}_minute"] : $submittedValues['minute'];
		$submittedValues['period'] = isset($values["{$name}_period"]) ? $values["{$name}_period"] : $submittedValues['period'];

		$fieldId = isset($attr['input']['attr']['id'])
			? $attr['input']['attr']['id']
			: $helper->getFieldId($inputName);
		if ($settings->options->class) {
			$helper->addAttrClass($attr, 'input', $settings->options->class);
		}
		$helper->addAttrClass($attr, 'input', $fieldId);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		//--attributes
		if ($settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		if ($settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$output = '';
		$inputClasses = $attr['input']['attr']['class'];

		// hour
		$attr['input']['attr']['name'] = $helper->getFieldSuffixId($inputName, '_hour');
		$attr['input']['attr']['id'] = "{$fieldId}_hour";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['input']}-hour";
		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');
		$output .= "\n\t\t<select{$inputAttr}>";
		$output .= "\n\t\t\t<option value=\"\" id=\"{$fieldId}_hour_none\">--</option>";
		for ($i = 1; $i <= 12; $i++) {
			$selected = sprintf("%02d", $i) == $submittedValues['hour'] ? ' selected="selected"' : '';
			$output .= "\n\t\t\t<option value=\"".sprintf("%02d", $i)."\" id=\"{$fieldId}_hour_".sprintf("%02d", $i)."\"{$selected}>".sprintf("%02d", $i)."</option>";
		}
		$output .= "\n\t\t</select>";

		// minute
		$attr['input']['attr']['name'] = $helper->getFieldSuffixId($inputName, '_minute');
		$attr['input']['attr']['id'] = "{$fieldId}_minute";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['input']}-minute";
		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');
		$output .= "<select{$inputAttr}>";
		$output .= "\n\t\t\t<option value=\"\" id=\"{$fieldId}_minute_none\">--</option>";
		for ($i = 0; $i <= 59; $i++) {
			$selected = sprintf("%02d", $i) == $submittedValues['minute'] ? ' selected="selected"' : '';
			$output .= "\n\t\t\t<option value=\"".sprintf("%02d", $i)."\" id=\"{$fieldId}_minute_".sprintf("%02d", $i)."\"{$selected}>".sprintf("%02d", $i)."</option>";
		}
		$output .= "\n\t\t</select>";

		// period
		$attr['input']['attr']['name'] = $helper->getFieldSuffixId($inputName, '_period');
		$attr['input']['attr']['id'] = "{$fieldId}_period";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['input']}-period";
		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');
		$amSelected = 'AM' == $submittedValues['period'] ? ' selected="selected"' : '';
		$pmSelected = 'PM' == $submittedValues['period'] ? ' selected="selected"' : '';
		$output .= "<select{$inputAttr}>";
		$output .= "\n\t\t\t<option value=\"AM\" id=\"{$fieldId}_period_am\"{$amSelected}>AM</option>";
		$output .= "\n\t\t\t<option value=\"PM\" id=\"{$fieldId}_period_pm\"{$pmSelected}>PM</option>";
		$output .= "\n\t\t</select>";

		return "<div class=\"subInputWrap\">{$output}</div>\n\t";
	}
}