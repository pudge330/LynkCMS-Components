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
use Lynk\Component\Form\Validator\BasicDataValidator;

/**
 * File input type.
 */
class FileInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'fileField';

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		if ($settings->options->accept) {
			$accepted = explode(',', $settings->options->accept);
			$fileIndex = BasicDataValidator::getFileIndex();
			if (sizeof($accepted) > 0 && $accepted[0] != '*') {
				for ($i = 0; $i < sizeof($accepted); $i++) {
					if (array_key_exists($accepted[$i], $fileIndex)) {
						$settings->options->fileTypes .= implode('|', $fileIndex[$accepted[$i]]['types']);
						$settings->options->fileExts .= '.' . implode('|.', $fileIndex[$accepted[$i]]['exts']);
						if ($i < sizeof($accepted) - 1) {
							$settings->options->fileTypes .= '|';
							$settings->options->fileExts .= '|';
						}
					}
				}
			}
		}
		return $settings;
	}

	/**
	 * Validate submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Boolean as first value that indicates whether or not the value was valid.
	 *               Second ootional value describes the error.
	 */
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || (!isset($data[$this->name]['error']) || $data[$this->name]['error'] == 4)))
			return [false, "{$displayName} is required"];
		else if ($data[$this->name] && $data[$this->name]['error'] == 0) {
			$fileResult = $this->validator->file(
				$data[$this->name]['tmp_name'], 
				$this->settings->options->accept,
				$this->settings->options->maxsize,
				strtolower(pathinfo($data[$this->name]['name'], PATHINFO_EXTENSION))
			);
			if (!$fileResult && $this->settings->options->accept) {
				$type = explode(',', $this->settings->options->accept);
				$last = array_pop($type);
				$type = implode(', ', $type) . " or {$last} file";
				if ($this->settings->options->maxsize)
					$type .= " and smaller than " . $this->helper->formatFileSize($this->settings->options->maxsize);
				return [false, "{$displayName} must be a {$type}"];
			}
			else
				[true];
		}
		return [true];
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
		$attr['input']['attr']['type'] = 'file';
		$attr['input']['attr']['value'] = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		if (is_array($attr['input']['attr']['value']))
			$attr['input']['attr']['value'] = '';

		//--id, class
		$attr['input']['attr']['id'] = $this->helper->getFieldId($this->inputName);
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $attr['input']['attr']['id']);
		$this->helper->addAttrClass($attr, 'input', $classes['input']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->required)
			$attr['input']['attr']['required'] = 'required';
		$attr['input']['attr']['accept'] = '';
		if ($this->settings->options->fileTypes)
			$attr['input']['attr']['accept'] = $this->settings->options->fileTypes;
		if ($this->settings->options->fileExts)
			$attr['input']['attr']['accept'] = ($attr['input']['attr']['accept'] == '' ? '' : '|') . $this->settings->options->fileExts;
		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$inputAttr = \lynk\attributes($attr['input']['attr']);
		$inputDataAttr = \lynk\attributes($attr['input']['dataAttr'], 'data-');

		return "<input{$inputAttr}{$inputDataAttr}>";
	}
}