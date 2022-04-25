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
class SelectView extends InputView {

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

		$attrKey = 'input';

		//--name, value(s)
		$attr[$attrKey]['attr']['name'] = $inputName;
		$submittedValues = $helper->getDefaultValues($settings, $values, $name);
		if ($submittedValues === null)
			$submittedValues = [];
		else if (!is_array($submittedValues))
			$submittedValues = [$submittedValues];

		//--id, class
		$fieldId = isset($attr[$attrKey]['attr']['id'])
			? $attr[$attrKey]['attr']['id']
			: $helper->getFieldId($inputName);
		$attr[$attrKey]['attr']['id'] = $fieldId;
		if ($settings->options->class) {
			$helper->addAttrClass($attr, $attrKey, $settings->options->class);
		}
		$helper->addAttrClass($attr, $attrKey, $fieldId);
		$attr[$attrKey]['attr']['class'] = trim($attr[$attrKey]['attr']['class']);

		//--attributes
		if ($settings->options->required)
			$attr[$attrKey]['attr']['required'] = 'required';
		if ($settings->options->multiple) {
			$attr['input']['attr']['name'] .= '[]';
			$attr['input']['attr']['multiple'] = 'multiple';
			$helper->addAttrClass($attr, 'wrap', 'multi' . ucfirst($fieldName));
		}
		if ($settings->options->disabled)
			$attr[$attrKey]['attr']['disabled'] = 'disabled';
		if ($settings->options->resiable)
			$attr['input']['dataAttr']['resiable'] = 'true';

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputAttr .= \lynk\attributes($attr['input']['dataAttr'], 'data-');

		$output = '';
		$count = -1;
		foreach ($settings->options->data as $dataKey => $dataValue) {
			$count++;
			$selected = in_array($dataKey, $submittedValues) ? ' selected="selected"' : '';
			$output .= "\n\t\t<option value=\"{$dataKey}\" id=\"{$fieldId}_{$count}\"{$selected}>{$dataValue}</option>";
		}
		return "<select{$inputAttr}>{$output}\n\t</select>";
	}
}