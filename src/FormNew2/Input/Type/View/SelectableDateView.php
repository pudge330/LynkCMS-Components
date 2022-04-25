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
class SelectableDateView extends InputView {

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
		$submittedValues = array('year' => null, 'month' => null, 'day' => null);
		$submittedValue = $helper->getDefaultValues($settings, $values, $name);
		if ($submittedValue) {
			$submittedDatetime = $helper->convertToDateTime($submittedValue);
			$submittedValues['year'] = $submittedDatetime->format('Y');
			$submittedValues['month'] = $submittedDatetime->format('m');
			$submittedValues['day'] = $submittedDatetime->format('d');
		}
		$submittedValues['year'] = isset($values["{$name}_year"]) ? $values["{$name}_year"] : $submittedValues['year'];
		$submittedValues['month'] = isset($values["{$name}_month"]) ? $values["{$name}_month"] : $submittedValues['month'];
		$submittedValues['day'] = isset($values["{$name}_day"]) ? $values["{$name}_day"] : $submittedValues['day'];
		if (!$submittedValues['year']) {
			$submittedValues['year'] = date('Y');
		}

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

		// month input
		$attr['input']['attr']['name'] = $helper->getFieldSuffixId($inputName, '_month');
		$attr['input']['attr']['id'] = "{$fieldId}_month";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['input']}-month";
		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');
		$output .= "\n\t\t<select{$inputAttr}>";
		$output .= "\n\t\t\t<option value=\"\" id=\"{$fieldId}_month_none\">--</option>";
		foreach (array(
			'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
		) as $dateKey => $dateValue) {
			$dateKey = (int)$dateKey + 1;
			$selected = sprintf("%02d", $dateKey) == $submittedValues['month'] ? ' selected="selected"' : '';
			$output .= "\n\t\t\t<option value=\"".sprintf("%02d", $dateKey)."\" id=\"{$fieldId}_month_".sprintf("%02d", $dateKey)."\"{$selected}>{$dateValue}</option>";
		}
		$output .= "\n\t\t</select>";

		// day input
		$attr['input']['attr']['name'] = $helper->getFieldSuffixId($inputName, '_day');
		$attr['input']['attr']['id'] = "{$fieldId}_day";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['input']}-day";
		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');
		$output .= "<select{$inputAttr}>";
		$output .= "\n\t\t\t<option value=\"\" id=\"{$fieldId}_day_none\">--</option>";
		for ($i = 1; $i <= 31; $i++) {
			$selected = sprintf("%02d", $i) == $submittedValues['day'] ? ' selected="selected"' : '';
			$output .= "\n\t\t\t<option value=\"".sprintf("%02d", $i)."\" id=\"{$fieldId}_day_".sprintf("%02d", $i)."\"{$selected}>".sprintf("%02d", $i)."</option>";
		}
		$output .= "\n\t\t</select>";

		// year input
		$attr['input']['attr']['name'] = $helper->getFieldSuffixId($inputName, '_year');
		$attr['input']['attr']['id'] = "{$fieldId}_year";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['input']}-year";
		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');
		$output .= "<select{$inputAttr}>";
		$output .= "\n\t\t\t<option value=\"\" id=\"{$fieldId}_year_none\">--</option>";
		for ($i = $settings->options->startYear; $i <= $settings->options->endYear; $i++) {
			$selected = sprintf("%04d", $i) == $submittedValues['year'] ? ' selected="selected"' : '';
			$output .= "\n\t\t\t<option value=\"".sprintf("%04d", $i)."\" id=\"{$fieldId}_year_".sprintf("%04d", $i)."\"{$selected}>".sprintf("%04d", $i)."</option>";
		}
		$output .= "\n\t\t</select>";

		return "<div class=\"subInputWrap\">{$output}</div>\n\t";
	}
}