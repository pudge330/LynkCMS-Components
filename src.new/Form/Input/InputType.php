<?php

//--move label for attr to renderLabel function
//--move all wrap modifiers (classes) to new overridable function

namespace LynkCMS\Component\Form\Input;

use LynkCMS\Component\Form\OptionTrait;
use LynkCMS\Component\Form\Input\InputHelper;
use LynkCMS\Component\Form\Input\SettingsContainer;
use LynkCMS\Component\Form\Validator\BasicDataValidator;

class InputType {
	protected static $serviceContainer = null;
	protected $name;
	protected $inputName;
	protected $settings;
	protected $val;
	protected $helper;
	protected $fieldName = 'textField';
	public function __construct($name, $inputName, SettingsContainer $settings) {
		$this->name = $name;
		$this->inputName = $inputName;
		$this->val = new BasicDataValidator();
		$this->helper = new InputHelper($this->val);
		$this->settings = $settings;
		$this->settings = $this->processSettings($settings);
	}
	public function processData($data) {
		return $data;
	}
	public function validateData($data) {
		return [true];
	}
	protected function processSettings($settings) {
		return $settings;
	}
	public function outputHtml($values = [], &$errors = []) {
		$attr = $this->helper->processFieldAttributes($this->settings);
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
	public function renderLabel(&$attr, $values = [], &$errors = []) {
		$attr['label']['attr']['for'] = $this->helper->getFieldId($this->inputName);
		return $this->settings->label && !$this->settings->options->noLabel ? $this->settings->label : null;
	}
	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);

		//--name, type and value
		$attr['input']['attr']['name'] = $this->inputName;
		$attr['input']['attr']['type'] = $this->settings->options->inputType ?: 'text';
		$attr['input']['attr']['value'] = $this->helper->getDefaultValues($this->settings, $values);

		//--id, class
		$attr['input']['attr']['id'] = $this->helper->getFieldId($this->inputName);
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $attr['input']['attr']['id']);
		$this->helper->addAttrClass($attr, 'input', $classes['input']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		if ($this->settings->options->readonly)
			$attr['input']['attr']['readonly'] = 'readonly';
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';
		if ($this->settings->options->max)
			$attr['input']['attr']['maxlength'] = $this->settings->options->max;
		if ($this->settings->options->placeholder)
			$attr['input']['attr']['placeholder'] = $this->settings->options->placeholder;

		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		return "<input{$inputAttr}{$inputDataAttr}>";
	}
	public function renderHelp(&$attr, $values = [], &$errors = []) {
		return $this->settings->help ? $this->settings->help : null;
	}
	public function renderError(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);
		$hasError = isset($errors[$this->name]);
		$error = $hasError ? $errors[$this->name] : null;
		unset($errors[$this->name]);
		if ($hasError) {
			$this->helper->addAttrClass($attr, 'wrap', "errorField");
		}
		return $error;
	}
	public function renderJSONData(&$attr, $values = [], &$errors = []) {
	}
	public function modifyAttributes(&$attr, $values = [], &$errors = []) {
		$this->helper->addAttrClass($attr, 'wrap', $this->fieldName);
		if ($this->settings->options->required)
			$this->helper->addAttrClass($attr, 'wrap', "requiredField");
		if ($this->settings->options->readonly)
			$this->helper->addAttrClass($attr, 'wrap', "readonlyField");
		if ($this->settings->options->disabled)
			$this->helper->addAttrClass($attr, 'wrap', "disabledField");
		if ($this->settings->options->fullField)
			$this->helper->addAttrClass($attr, 'wrap', "fullField");
		if ($this->settings->options->fullLabel)
			$this->helper->addAttrClass($attr, 'wrap', "fullLabelField");
		if ($this->settings->options->sroLabel)
			$this->helper->addAttrClass($attr, 'wrap', "sroField");
	}
	public function buildFieldStructure($attr, $parts) {
		$baseParts = array(
			'label' => null
			,'input' => null
			,'help' => null
			,'error' => null
			,'data' => null
		);
		if ($this->settings->options->helpAbove) {
			$baseParts = array(
				'label' => null
				,'help' => null
				,'input' => null
				,'error' => null
				,'data' => null
			);
		}
		$parts = array_merge($baseParts, $parts);
		$classes = $this->getFormFieldClasses($this->settings);
		$field = '';
		$fieldParts = $baseParts;
		foreach (array_keys($baseParts) as $key) {
			if ($parts[$key]) {
				$field .= $this->renderWrap($key, $parts[$key], $this->settings, $attr);
			}
		}
		$field = $this->renderWrap('wrap', $field, $this->settings, $attr);
		return $field;
	}
	public function renderFieldWrap($part, $settings, $attr) {
		$tmpAttr = $this->helper->buildAttributeString($attr['wrap']['attr']);
		$tmpDataAttr = $this->helper->buildAttributeString($attr['wrap']['dataAttr'], 'data-');
		return "<div{$tmpAttr}{$tmpDataAttr}>\n{$part}</div>\n";
	}
	public function renderLabelWrap($part, $settings, $attr) {
		$tmpAttr = $this->helper->buildAttributeString($attr['label']['attr']);
		$tmpDataAttr = $this->helper->buildAttributeString($attr['label']['dataAttr']);
		$for = isset($attr['label']['attr']['for']) ? " for=\"{$attr['label']['attr']['for']}\"" : '';
		$tmpAttr = $attr['label']['attr'];
		unset($tmpAttr['for']);
		$tmpAttr = $this->helper->buildAttributeString($tmpAttr);
		return "\t<div{$tmpAttr}{$tmpDataAttr}><label{$for}>{$part}</label></div>\n";
	}
	public function renderInputWrap($part, $settings, $attr) {
		$classes = $this->getFormFieldClasses($settings);
		$tmpAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$tmpDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr']);
		if (isset($attr["inputWrap"]['attr']['class']))
			$attr["inputWrap"]['attr']['class'] = "{$attr["inputWrap"]['attr']['class']} {$classes["inputWrap"]}";
		else
			$attr["inputWrap"]['attr']['class'] = $classes["inputWrap"];
		$tmpAttr = $this->helper->buildAttributeString($attr["inputWrap"]['attr']);
		$tmpDataAttr = $this->helper->buildAttributeString($attr["inputWrap"]['dataAttr']);
		return "\t<div{$tmpAttr}{$tmpDataAttr}>{$part}</div>\n";
	}
	public function renderHelpWrap($part, $settings, $attr) {
		$tmpAttr = $this->helper->buildAttributeString($attr['help']['attr']);
		$tmpDataAttr = $this->helper->buildAttributeString($attr['help']['dataAttr']);
		return "\t<div{$tmpAttr}{$tmpDataAttr}><span>{$part}</span></div>\n";
	}
	public function renderErrorWrap($part, $settings, $attr) {
		$tmpAttr = $this->helper->buildAttributeString($attr['error']['attr']);
		$tmpDataAttr = $this->helper->buildAttributeString($attr['error']['dataAttr']);
		return "\t<div{$tmpAttr}{$tmpDataAttr}><span>{$part}</span></div>\n";
	}
	public function renderWrap($type, $part, $settings, $attr) {
		$classes = $this->getFormFieldClasses($settings);
		if (isset($attr[$type]['attr']['class']))
			$attr[$type]['attr']['class'] = "{$attr[$type]['attr']['class']} {$classes[$type]}";
		else
			$attr[$type]['attr']['class'] = $classes[$type];
		$field = '';
		switch ($type) {
			case 'wrap':
				$field = $this->renderFieldWrap($part, $settings, $attr);
				break;
			case 'label':
				$field = $this->renderLabelWrap($part, $settings, $attr);
				break;
			case 'input':
				$field = $this->renderInputWrap($part, $settings, $attr);
				break;
			case 'help':
				$field = $this->renderHelpWrap($part, $settings, $attr);
				break;
			case 'error':
				$field = $this->renderErrorWrap($part, $settings, $attr);
				break;
			case 'data':
				$tmpAttr = $this->helper->buildAttributeString($attr['data']['attr']);
				$tmpDataAttr = $this->helper->buildAttributeString($attr['data']['dataAttr']);
				$field = "\t<script{$tmpAttr}{$tmpDataAttr} type=\"application/json\"><!--{$part}--></script>\n";
				break;
		}
		return $field;
	}
	public function getSetting($name) {
		return $this->settings->get($name);
	}
	public function setSetting($name, $setting) {
		$this->settings->set($name, $setting);
	}
	public function hasSetting($name) {
		return $this->settings->has($name);
	}
	public function getSettings() {
		return $this->settings;
	}
	public function getOption($name) {
		return $this->settings->options->get($name);
	}
	public function setOption($name, $option) {
		$this->settings->options->set($name, $option);
	}
	public function hasOption($name) {
		return $this->settings->options->has($name);
	}
	public function getOptions() {
		return $this->settings->options;
	}
	protected function getFormFieldClasses() {
		return [
			'wrap' => 'formField'
			,'label' => 'fieldLabel'
			,'sublabel' => 'fieldSubLabel'
			,'input' => 'fieldInput'
			,'subinput' => 'fieldSubInput'
			,'inputWrap' => 'fieldWrap'
			,'help' => 'fieldHelp'
			,'error' => 'fieldError'
			,'data' => 'fieldData'
		];
	}
	public static function setServiceContainer($container) {
		static::$serviceContainer = $container;
	}
	public static function getServiceContainer() {
		return static::$serviceContainer;
	}
	public function getContainer() {
		return static::getServiceContainer();
	}
}