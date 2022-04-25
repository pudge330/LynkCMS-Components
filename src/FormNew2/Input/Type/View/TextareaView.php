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
 * Textarea view class.
 */
class TextareaView extends InputView {

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

		//--name, type and value
		$attr[$attrKey]['attr']['name'] = $inputName;
		$attr[$attrKey]['attr']['type'] = $settings->options->inputType ?: 'text';
		$submittedValue = htmlentities($helper->getDefaultValues($settings, $values, $name));

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
		if ($settings->options->readonly)
			$attr[$attrKey]['attr']['readonly'] = 'readonly';
		if ($settings->options->disabled)
			$attr[$attrKey]['attr']['disabled'] = 'disabled';
		if ($settings->options->min)
			$attr['input']['attr']['min'] = $settings->options->min;
		if ($settings->options->max)
			$attr[$attrKey]['attr']['maxlength'] = $settings->options->max;
		if ($settings->options->placeholder)
			$attr[$attrKey]['attr']['placeholder'] = $settings->options->placeholder;

		$inputAttr = \lynk\attributes($attr[$attrKey]['attr']);
		$inputAttr .= \lynk\attributes($attr[$attrKey]['dataAttr'], 'data-');
		return "<textarea{$inputAttr}>{$submittedValue}</textarea>";
	}
}