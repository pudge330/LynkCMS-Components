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
 * Select input type.
 */
class SelectInput extends CheckboxInput {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'selectField';

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
		$attr['input']['attr']['name'] = $this->inputName;
		$submittedValues = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		if ($submittedValues === null)
			$submittedValues = [];
		else if (!is_array($submittedValues))
			$submittedValues = [$submittedValues];

		//--id, class
		$fieldId = $this->helper->getFieldId($this->inputName);
		$attr['input']['attr']['id'] = $fieldId;
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $fieldId);
		$this->helper->addAttrClass($attr, 'input', $classes['input']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		if ($this->settings->options->multiple) {
			$attr['input']['attr']['name'] .= '[]';
			$attr['input']['attr']['multiple'] = 'multiple';
			$this->helper->addAttrClass($attr, 'wrap', 'multi' . ucfirst($this->fieldName));
		}
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		if ($this->settings->options->resiable)
			$attr['input']['dataAttr']['resiable'] = 'true';

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');

		$output = '';
		$count = -1;
		foreach ($this->settings->options->data as $dataKey => $dataValue) {
			$count++;
			$selected = in_array($dataKey, $submittedValues) ? ' selected="selected"' : '';
			$output .= "<option value=\"{$dataKey}\" id=\"{$fieldId}_{$count}\"{$selected}>{$dataValue}</option>";
		}
		return "<select{$inputAttr}{$inputDataAttr}>{$output}</select>";
	}
}