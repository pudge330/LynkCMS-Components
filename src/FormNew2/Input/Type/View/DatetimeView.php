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
class DatetimeView extends InputView {

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
		// build attributes
		$labelAttr = \lynk\attributes($attr['label']['attr']);
		$labelAttr .= \lynk\attributes($attr['label']['dataAttr'], 'data-');

		// label text
		$labelText = $this->input->getSettings()->label && !$this->input->getSettings()->options->noLabel
			? $this->input->getSettings()->label
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

		//--name, type and value
		$attr['input']['attr']['name'] = $inputName;
		$submittedDate = $submittedTime = null;
		$submittedValue = $helper->getDefaultValues($settings, $values, $name);
		if ($submittedValue) {
			$submittedDatetime = $helper->convertToDateTime($submittedValue);
			$submittedDate = $submittedDatetime->format('Y-m-d');
			$submittedTime = $submittedDatetime->format('H:i');
		}
		$submittedDate = isset($values["{$name}_date"]) ? $values["{$name}_date"] : $submittedDate;
		$submittedTime = isset($values["{$name}_time"]) ? $values["{$name}_time"] : $submittedTime;

		//--id, class
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
		if ($settings->options->min)
			$attr['input']['attr']['min'] = $settings->options->min;
		if ($settings->options->max)
			$attr['input']['attr']['max'] = $settings->options->max;
		if ($settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$dateAttributes = $attr['input']['attr'];
		$timeAttributes = $attr['input']['attr'];

		//--date
		$dateAttributes['name'] = $helper->getFieldSuffixId($inputName, '_date');
		$dateAttributes['type'] = 'date';
		$dateAttributes['id'] = "{$fieldId}_date";
		$dateAttributes['class'] .= " {$fieldId}_date";
		if ($submittedDate)
			$dateAttributes['value'] = $submittedDate;
		$attr['input']['dataAttr']['dataid'] = "{$fieldId}_date";
		$dateInputAttr = \lynk\attributes($dateAttributes);
		$dateInputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');

		//--time
		$timeAttributes['name'] = $helper->getFieldSuffixId($inputName, '_time');
		$timeAttributes['type'] = 'time';
		$timeAttributes['id'] = "{$fieldId}_time";
		$timeAttributes['class'] .= " {$fieldId}_time";
		if ($submittedDate)
			$timeAttributes['value'] = $submittedDate;
		$attr['input']['dataAttr']['dataid'] = "{$fieldId}_time";
		$timeInputAttr = \lynk\attributes($timeAttributes);
		$timeInputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');

		$wrapAttributes = $attr['subInputWrap']['attr'];
		$wrapAttr = \lynk\attributes($wrapAttributes);
		$wrapAttr .= \lynk\attributes($attr['subInputWrap']['dataAttr'], 'data-');

		$output = "\n\t\t<div{$wrapAttr}><input{$dateInputAttr}></div>\n\t\t<div{$wrapAttr}><input{$timeInputAttr}></div>";
		return $output . "\n\t";
	}
}