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
 * Color view class.
 */
class CheckboxView extends InputView {

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
		$inputName = $this->input->getInputName();
		$settings = $this->input->getSettings();
		$helper = $this->input->getHelper();

		// set for attribute
		$attr['label']['attr']['for'] = isset($attr['input']['attr']['id'])
			? $attr['input']['attr']['id']
			: $helper->getFieldId($inputName);

		if (sizeof($settings->options->data) > 1 || $settings->multiple) {
			unset($attr['label']['attr']['for']);
		}

		// build attributes
		$labelAttr = \lynk\attributes($attr['label']['attr']);
		$labelAttr .= \lynk\attributes($attr['label']['dataAttr'], 'data-');

		$labelText = $settings->label && !$settings->options->noLabel ? $settings->label : null;

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

		//--name, type, value(s)
		$attr['input']['attr']['name'] = $inputName;
		$attr['input']['attr']['type'] = 'checkbox';
		$submittedValues = $helper->getDefaultValues($settings, $values, $name);
		if ($submittedValues === null)
			$submittedValues = [];
		else if (!is_array($submittedValues))
			$submittedValues = [$submittedValues];

		//--id, class
		$fieldId = isset($attr['input']['attr']['id'])
			? $attr['input']['attr']['id']
			: $helper->getFieldId($inputName);
		if ($settings->options->class) {
			$helper->addAttrClass($attr, 'input', $settings->options->class);
		}
		$helper->addAttrClass($attr, 'input', $fieldId);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		//--wrap attributes
		if ($settings->options->layout) {
			$attr['wrap']['dataAttr']['layout'] = $settings->options->layout;
		}

		//--attributes
		if ($settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		if ($settings->options->readonly)
			$attr['input']['attr']['readonly'] = 'readonly';
		if ($settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		//--multiple
		if (sizeof($settings->options->data) > 1)
			$settings->options->multiple = true;
		if ($settings->options->multiple)
			$attr['input']['attr']['name'] .= '[]';

		//--output
		$output = '';
		$count = -1;
		foreach ($settings->options->data as $dataKey => $dataValue) {
			$count++;
			$inputAttributes = $attr['input']['attr'];
			$labelAttributes = $attr['subLabel']['attr'];
			$wrapAttributes = $attr['subInputWrap']['attr'];
			
			$inputAttributes['value'] = htmlentities($dataKey);
			$inputAttributes['id'] = $fieldId . "_{$count}";
			if (in_array($dataKey, $submittedValues)) {
				$inputAttributes['checked'] = 'checked';
			}
			$inputAttr = \lynk\attributes($inputAttributes);
			$inputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');

			$labelAttributes['for'] = "{$inputAttributes['id']}";
			$labelAttr = \lynk\attributes($labelAttributes);
			$labelAttr .= \lynk\attributes($attr['subLabel']['dataAttr'], 'data-');

			$wrapAttr = \lynk\attributes($wrapAttributes);
			$wrapAttr .= \lynk\attributes($attr['subInputWrap']['dataAttr'], 'data-');

			$output .= "<div{$wrapAttr}><input{$inputAttr}> <label{$labelAttr}>{$dataValue}</label></div>";
		}

		return $output;
	}
}