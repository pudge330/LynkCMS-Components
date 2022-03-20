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

namespace Lynk\Component\Form\Input;

use Lynk\Component\Container\StandardContainer;
use Lynk\Component\Form\Input\InputHelper;
use Lynk\Component\Form\Validator\BasicDataValidator;

/**
 * Form input type class.
 */
class InputType {

	/**
	 * @var Lynk\Component\Container\Container Service container.
	 */
	protected static $serviceContainer = null;

	/**
	 * @var string Input key
	 */
	protected $name;

	/**
	 * @var string Input name.
	 */
	protected $inputName;

	/**
	 * @var StandardContainer Input settings.
	 */
	protected $settings;

	/**
	 * @var mixed Input value.
	 */
	protected $validator;

	/**
	 * @var BasicDataValidator Data validator class.
	 */
	protected $helper;

	/**
	 * @var string Field type name.
	 */
	protected $fieldName = 'textField';

	/**
	 * @param string $name Field key.
	 * @param string $inputName Field name attribute.
	 * @param StandardContainer $settings Field settings.
	 */
	public function __construct($name, $inputName, StandardContainer $settings) {
		$this->name = $name;
		$this->inputName = $inputName;
		$this->validator = new BasicDataValidator();
		$this->helper = new InputHelper($this->validator);
		$this->settings = $settings;
		$this->settings = $this->processSettings($settings);
	}

	/**
	 * Process submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Data values.
	 */
	public function processData(Array $data) {
		return $data;
	}

	/**
	 * Validate submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array An array of input errors. Enpty array or null indicates successful data validation.
	 */
	public function validateData($data) {
		return null;
	}

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	protected function processSettings($settings) {
		return $settings;
	}

	/**
	 * Get setting.
	 * 
	 * @param string $name Setting name.
	 * 
	 * @return mixed Setting value.
	 */
	public function getSetting($name) {
		return $this->settings->get($name);
	}

	/**
	 * Set setting.
	 * 
	 * @param string $name Setting name.
	 * @param mixed $setting Setting value
	 */
	public function setSetting($name, $setting) {
		$this->settings->set($name, $setting);
	}

	/**
	 * Check if setting exists.
	 * 
	 * @param string $name Setting name.
	 * 
	 * @return bool True if setting exists, false otherwise.
	 */
	public function hasSetting($name) {
		return $this->settings->has($name);
	}

	/**
	 * Get all settings.
	 * 
	 * @return StandardContainer Settings container object.
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * Get option.
	 * 
	 * @param string $name Option name.
	 * 
	 * @return mixed Option value.
	 */
	public function getOption($name) {
		return $this->settings->options->get($name);
	}

	/**
	 * Set option.
	 * 
	 * @param string $name Option name.
	 * @param mixed $option Option value.
	 */
	public function setOption($name, $option) {
		$this->settings->options->set($name, $option);
	}

	/**
	 * Check if option exists.
	 * 
	 * @param string $name Option name.
	 * 
	 * @return bool True if option exists, false otherwise.
	 */
	public function hasOption($name) {
		return $this->settings->options->has($name);
	}

	/**
	 * Get all options.
	 * 
	 * @return StandardContainer Options container object.
	 */
	public function getOptions() {
		return $this->settings->options;
	}

	/**
	 * Get individual input part class names.
	 * 
	 * @return Array List of classes for parts.
	 */
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

	/**
	 * Modify attributes for specific instances.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 */
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

	/**
	 * Build input field structure.
	 * 
	 * @param Array $attr Inout attributes.
	 * @param Array $parts Individual rendered input parts.
	 * 
	 * @return string Rendered HTML code with all parts.
	 */
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
		foreach (array_keys($baseParts) as $key) {
			if ($parts[$key]) {
				$field .= $this->renderWrap($key, $parts[$key], $this->settings, $attr);
			}
		}
		$field = $this->renderWrap('wrap', $field, $this->settings, $attr);
		return $field;
	}

	/**
	 * Output input HTML code.
	 * 
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered input HTML code.
	 */
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
		$attr['label']['attr']['for'] = $this->helper->getFieldId($this->inputName);
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

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');
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
		return $this->settings->help ? $this->settings->help : null;
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
		$classes = $this->getFormFieldClasses($this->settings);
		$hasError = isset($errors[$this->name]);
		$error = $hasError ? $errors[$this->name] : null;
		unset($errors[$this->name]);
		if ($hasError) {
			$this->helper->addAttrClass($attr, 'wrap', "errorField");
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
		$tmpAttr = \lynk\attributes($attr['label']['attr']);
		$tmpDataAttr = \lynk\attributes($attr['label']['dataAttr']);
		$for = isset($attr['label']['attr']['for']) ? " for=\"{$attr['label']['attr']['for']}\"" : '';
		$tmpAttr = $attr['label']['attr'];
		unset($tmpAttr['for']);
		$tmpAttr = \lynk\attributes($tmpAttr);
		return "\t<div{$tmpAttr}{$tmpDataAttr}><label{$for}>{$part}</label></div>\n";
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
		$classes = $this->getFormFieldClasses($settings);
		$tmpAttr = \lynk\attributes($attr['input']['attr']);
		$tmpDataAttr = \lynk\attributes($attr['input']['dataAttr']);
		if (isset($attr["inputWrap"]['attr']['class']))
			$attr["inputWrap"]['attr']['class'] = "{$attr["inputWrap"]['attr']['class']} {$classes["inputWrap"]}";
		else
			$attr["inputWrap"]['attr']['class'] = $classes["inputWrap"];
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
		return "\t<div{$tmpAttr}{$tmpDataAttr}><span>{$part}</span></div>\n";
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
		return "\t<div{$tmpAttr}{$tmpDataAttr}><span>{$part}</span></div>\n";
	}

	/**
	 * Render wrap.
	 * 
	 * @param string $type Input part type.
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
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
				$tmpAttr = \lynk\attributes($attr['data']['attr']);
				$tmpDataAttr = \lynk\attributes($attr['data']['dataAttr']);
				$field = "\t<script{$tmpAttr}{$tmpDataAttr} type=\"application/json\"><!--{$part}--></script>\n";
				break;
		}
		return $field;
	}

	/**
	 * Get service container.
	 * 
	 * @return Lynk\Component\Container\Container Service container.
	 */
	public function getContainer() {
		return static::getServiceContainer();
	}

	/**
	 * Set service container.
	 * 
	 * @param Lynk\Component\Container\Container Service container.
	 */
	public static function setServiceContainer($container) {
		static::$serviceContainer = $container;
	}

	/**
	 * Get service container.
	 * 
	 * @return Lynk\Component\Container\Container Service container.
	 */
	public static function getServiceContainer() {
		return static::$serviceContainer;
	}
}