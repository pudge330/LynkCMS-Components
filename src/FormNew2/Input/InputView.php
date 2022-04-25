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
 * 
 * @todo sub-inputs like checkboxes, radios, selectable date/time/etc should use input attributes and regular classes.
 *       Sub labels and sub waps should use default classes as well and only use subLabel, subLabelWrap, subInputWrap for adding additional
 *       attributes without adding it to the primary elements.
 */

namespace Lynk\Component\FormNew2\Input;

/**
 * Form input type class.
 */
class InputView {
	protected $input;

	public function __construct(InputType $input) {
		$this->input = $input;
	}

	/**
	 * Applies form field classes to attribute array.
	 * 
	 * @param array &$attr Attributes array by reference.
	 * @param array $classes Classes to apply.
	 */
	public function applyFormFieldClasses(array &$attr, array $classes = null) {
		$helper = $this->input->getHelper();
		if (!$classes) {
			$classes = $this->getFieldClasses();
		}
		foreach (
			['wrap', 'labelWrap', 'label', 'inputWrap', 'input', 'subInputWrap', 'subLabel', 'subInput', 'help', 'error', 'data'] as $type
		) {
			if (isset($classes[$type])) {
				$helper->addAttrClass($attr, $type, $classes[$type], false);
			}
		}
	}

	/**
	 * Modify attributes for specific instances.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 */
	public function modifyAttributes(&$attr, $values = [], &$errors = []) {
		$fieldName = $this->input->getFieldName();
		$settings = $this->input->getSettings();
		$helper = $this->input->getHelper();
		$helper->addAttrClass($attr, 'wrap', $fieldName);
		if ($settings->options->required)
			$helper->addAttrClass($attr, 'wrap', "requiredField");
		if ($settings->options->readonly)
			$helper->addAttrClass($attr, 'wrap', "readonlyField");
		if ($settings->options->disabled)
			$helper->addAttrClass($attr, 'wrap', "disabledField");
		if ($settings->options->fullField)
			$helper->addAttrClass($attr, 'wrap', "fullField");
		if ($settings->options->fullLabel)
			$helper->addAttrClass($attr, 'wrap', "fullLabelField");
		if ($settings->options->sroLabel)
			$helper->addAttrClass($attr, 'wrap', "sroLabelField");
	}

	/**
	 * Build input field structure.
	 * 
	 * @param Array $attr Inout attributes.
	 * @param Array $parts Individual rendered input parts.
	 * 
	 * @return string Rendered HTML code with all parts.
	 */
	public function buildFieldStructure($attr, $parts) {
		$inputName = $this->input->getInputName();
		$settings = $this->input->getSettings();
		$helper = $this->input->getHelper();

		$baseParts = array(
			'label' => null
			,'input' => null
			,'help' => null
			,'error' => null
			,'data' => null
		);
		if ($settings->options->helpAbove) {
			$baseParts = array(
				'label' => null
				,'help' => null
				,'input' => null
				,'error' => null
				,'data' => null
			);
		}
		$parts = array_merge($baseParts, $parts);
		$field = '';
		foreach (array_keys($baseParts) as $key) {
			$ucfKey = ucfirst($key);
			if ($parts[$key]) {
				$field .= $this->{"render{$ucfKey}Wrap"}($parts[$key], $settings, $attr);
			}
		}
		$fieldId = $helper->getFieldId($inputName);
		$helper->addAttrClass($attr, 'wrap', "field_{$fieldId}");
		$field = $this->renderFieldWrap($field, $settings, $attr);
		return $field;
	}

	public function render($values = [], &$errors = []) {
		$settings = $this->input->getSettings();
		$helper = $this->input->getHelper();
		$attr = $helper->processFieldAttributes($settings);
		$this->applyFormFieldClasses($attr);
		$this->modifyAttributes($attr, $values, $errors);
		$parts = array(
			'label' => $this->renderLabel($attr, $values, $errors)
			,'input' => $this->renderInput($attr, $values, $errors)
			,'help' => $this->renderHelp($attr, $values, $errors)
			,'error' => $this->renderError($attr, $values, $errors)
			,'data' => $this->renderJSONData($attr, $values, $errors)
		);
		return $this->buildFieldStructure($attr, $parts);
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
		$name = $this->input->getName();
		$fieldName = $this->input->getFieldName();
		$inputName = $this->input->getInputName();
		$settings = $this->input->getSettings();
		$helper = $this->input->getHelper();

		// set for attribute
		$attr['label']['attr']['for'] = isset($attr['input']['attr']['id'])
			? $attr['input']['attr']['id']
			: $helper->getFieldId($inputName);

		// build attributes
		$labelAttr = \lynk\attributes($attr['label']['attr']);
		$labelAttr .= \lynk\attributes($attr['label']['dataAttr']);

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

		$attrKey = 'input';

		//--name, type and value
		$attr[$attrKey]['attr']['name'] = $inputName;
		$attr[$attrKey]['attr']['type'] = $settings->options->inputType ?: 'text';
		$submittedValue = $helper->getDefaultValues($settings, $values, $name);
		if ($submittedValue) {
			$attr[$attrKey]['attr']['value'] = htmlentities($submittedValue);
		}

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
		$inputDataAttr = \lynk\attributes($attr[$attrKey]['dataAttr'], 'data-');
		return "<input{$inputAttr}{$inputDataAttr}>";
	}

	/**
	 * Render input help.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered help.
	 */
	public function renderHelp(&$attr, $values = [], &$errors = []) {
		$settings = $this->input->getSettings();
		return $settings->help ? $settings->help : null;
	}

	/**
	 * Render input error.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered error.
	 */
	public function renderError(&$attr, $values = [], &$errors = []) {
		$name = $this->input->getName();
		$helper = $this->input->getHelper();

		$hasError = isset($errors[$name]);
		$error = $hasError ? $errors[$name] : null;
		unset($errors[$name]);
		if ($hasError) {
			$helper->addAttrClass($attr, 'wrap', "errorField");
		}
		return $error;
	}

	/**
	 * Render JSON data.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered JSON data.
	 */
	public function renderJSONData(&$attr, $values = [], &$errors = []) {
		return null;
	}

	/**
	 * Render field wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderFieldWrap($part, $settings, $attr) {
		$tmpAttr = \lynk\attributes($attr['wrap']['attr']);
		$tmpDataAttr = \lynk\attributes($attr['wrap']['dataAttr'], 'data-');
		return "<div{$tmpAttr}{$tmpDataAttr}>\n{$part}</div>\n";
	}

	/**
	 * Render label wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderLabelWrap($part, $settings, $attr) {
		$wrapAttr = \lynk\attributes($attr['labelWrap']['attr']);
		$wrapAttr .= \lynk\attributes($attr['labelWrap']['dataAttr']);
		return "\t<div{$wrapAttr}>{$part}</div>\n";
	}

	/**
	 * Render input wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderInputWrap($part, $settings, $attr) {
		$tmpAttr = \lynk\attributes($attr["inputWrap"]['attr']);
		$tmpDataAttr = \lynk\attributes($attr["inputWrap"]['dataAttr']);
		return "\t<div{$tmpAttr}{$tmpDataAttr}>{$part}</div>\n";
	}

	/**
	 * Render help wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderHelpWrap($part, $settings, $attr) {
		$tmpAttr = \lynk\attributes($attr['help']['attr']);
		$tmpDataAttr = \lynk\attributes($attr['help']['dataAttr']);
		return "\t<div{$tmpAttr}{$tmpDataAttr}>{$part}</div>\n";
	}

	/**
	 * Render error wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderErrorWrap($part, $settings, $attr) {
		$tmpAttr = \lynk\attributes($attr['error']['attr']);
		$tmpDataAttr = \lynk\attributes($attr['error']['dataAttr']);
		return "\t<div{$tmpAttr}{$tmpDataAttr}>{$part}</div>\n";
	}

	/**
	 * Render data wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderDataWrap($part, $settings, $attr) {
		$tmpAttr = \lynk\attributes($attr['data']['attr']);
		$tmpDataAttr = \lynk\attributes($attr['data']['dataAttr']);
		return "\t<script{$tmpAttr}{$tmpDataAttr} type=\"application/json\"><!--{$part}--></script>\n";
	}

	public function getFieldClasses() {
		return [
			'wrap' => 'formField'
			,'labelWrap' => 'fieldLabelWrap'
			,'label' => 'fieldLabel'
			,'inputWrap' => 'fieldWrap'
			,'input' => 'fieldInput'
			,'subInputWrap' => 'fieldWrap'
			,'subLabel' => 'fieldLabel'
			// ,'subInput' => 'fieldSubInput'
			,'help' => 'fieldHelp'
			,'error' => 'fieldError'
			,'data' => 'fieldData'
		];
	}
}