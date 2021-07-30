<?php
/**
 * This file is part of the LynkCMS Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS Components
 * @subpackage Form
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Form\Input\Type;

use Datetime;
use LynkCMS\Component\Form\Input\InputType;

/**
 * Selectable Date input type.
 */
class SelectableDateInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'selectableDateField';

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

		$min = $this->settings->options->min;
		$max = $this->settings->options->max;
		$min = $min ? Datetime::createFromFormat('Y-m-d H:i', $min . ' 00:00') : null;
		$max = $max ? Datetime::createFromFormat('Y-m-d H:i', $max . ' 00:00') : null;

		$yearValue = isset($data["{$this->name}_year"]) && $data["{$this->name}_year"] != '' ? $data["{$this->name}_year"] : null;
		$monthValue = isset($data["{$this->name}_month"]) && $data["{$this->name}_month"] != '' ? $data["{$this->name}_month"] : null;
		$dayValue = isset($data["{$this->name}_day"]) && $data["{$this->name}_day"] != '' ? $data["{$this->name}_day"] : null;
		$yearInRange = $yearValue && is_numeric($yearValue) && is_int((int)$yearValue);
		$monthInRange = $monthValue && ((int)$monthValue >= 1 && (int)$monthValue <= 12);
		$dayInRange = $dayValue && ((int)$dayValue >= 1 && (int)$dayValue <= 31);

		$allExists = ($yearValue && $monthValue && $dayValue);
		$isAllValid = ($yearInRange && $monthInRange && $dayInRange);

		if ($this->settings->options->required && !$allExists) {
			return [false, "{$displayName} is required"];
		}
		else if ($allExists && !$isAllValid) {
			return [false, "{$displayName} had invalid options"];
		}
		else if ($isAllValid) {
			$submittedObject = Datetime::createFromFormat('Y-m-d h:i A', "{$yearValue}-{$monthValue}-{$dayValue} 00:00 AM");
			$passedMinTest = $passedMaxTest = false;
			if ($min)
				$passedMinTest = ((int)$submittedObject->format('Ymd') >= (int)$min->format('Ymd'));
			if ($max)
				$passedMaxTest = ((int)$submittedObject->format('Ymd') <= (int)$max->format('Ymd'));
			$formattedMin = $min ? $min->format('F d, Y') : '';
			$formattedMax = $max ? $max->format('F d, Y') : '';
			if ($min && $max && (!$passedMinTest || !$passedMaxTest)) {
				return [false, "{$displayName} must be on or between {$formattedMin} and {$formattedMax}"];
			}
			else if ($min && !$passedMinTest) {
				return [false, "{$displayName} must be on or after {$formattedMin}"];
			}
			else if ($max && !$passedMaxTest) {
				return [false, "{$displayName} must be on or before {$formattedMax}"];
			}
		}
		return [true];
	}

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		$min = $settings->options->min ? Datetime::createFromFormat('Y-m-d H:i', $settings->options->min . ' 00:00') : null;
		$max = $settings->options->max ? Datetime::createFromFormat('Y-m-d H:i', $settings->options->max . ' 00:00') : null;
		$minYear = $min ? $min->format('Y') : 1975;
		$maxYear = $max ? $max->format('Y') : (int)date('Y') + 3;
		if ($settings->options->startYear === null)
			$settings->options->startYear = $minYear;
		if ($settings->options->endYear === null)
			$settings->options->endYear = $maxYear;
		return $settings;
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

		//--name, type, value(s)
		$submittedValues = array('year' => null, 'month' => null, 'day' => null);
		$submittedValue = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		if ($submittedValue) {
			$submittedDatetime = $this->helper->toDateTime($submittedValue);
			$submittedValues['year'] = $submittedDatetime->format('Y');
			$submittedValues['month'] = $submittedDatetime->format('m');
			$submittedValues['day'] = $submittedDatetime->format('d');
		}
		$submittedValues['year'] = isset($values["{$this->name}_year"]) ? $values["{$this->name}_year"] : $submittedValues['year'];
		$submittedValues['month'] = isset($values["{$this->name}_month"]) ? $values["{$this->name}_month"] : $submittedValues['month'];
		$submittedValues['day'] = isset($values["{$this->name}_day"]) ? $values["{$this->name}_day"] : $submittedValues['day'];
		if (!$submittedValues['year'])
			$submittedValues['year'] = date('Y');

		//--id, class
		$fieldId = $this->helper->getFieldId($this->inputName);
		if ($this->settings->options->class)
			$this->helper->addAttrClass($attr, 'input', $this->settings->options->class);
		$this->helper->addAttrClass($attr, 'input', $fieldId);
		$this->helper->addAttrClass($attr, 'input', $classes['subinput']);
		$attr['input']['attr']['class'] = trim($attr['input']['attr']['class']);

		if ($this->settings->options->disabled)
			$attr['input']['attr']['disabled'] = 'disabled';

		$inputClasses = $attr['input']['attr']['class'];

		//--date inputs
		$dateOutput = '';
		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_month');
		$attr['input']['attr']['id'] = "{$fieldId}_month";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-month";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"\" id=\"{$fieldId}_none\">--</option>";
		foreach (array(
			'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
		) as $dateKey => $dateValue) {
			$dateKey = (int)$dateKey + 1;
			$selected = sprintf("%02d", $dateKey) == $submittedValues['month'] ? ' selected="selected"' : '';
			$dateOutput .= "<option value=\"".sprintf("%02d", $dateKey)."\" id=\"{$fieldId}_".sprintf("%02d", $dateKey)."\"{$selected}>{$dateValue}</option>";
		}
		$dateOutput .= "</select>";

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_day');
		$attr['input']['attr']['id'] = "{$fieldId}_day";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-day";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"\" id=\"{$fieldId}_none\">--</option>";
		for ($i = 1; $i <= 31; $i++) {
			$selected = sprintf("%02d", $i) == $submittedValues['day'] ? ' selected="selected"' : '';
			$dateOutput .= "<option value=\"".sprintf("%02d", $i)."\" id=\"{$fieldId}_".sprintf("%02d", $i)."\"{$selected}>".sprintf("%02d", $i)."</option>";
		}
		$dateOutput .= "</select>";

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_year');
		$attr['input']['attr']['id'] = "{$fieldId}_year";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-year";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"\" id=\"{$fieldId}_none\">--</option>";
		for ($i = $this->settings->options->startYear; $i <= $this->settings->options->endYear; $i++) {
			$selected = sprintf("%04d", $i) == $submittedValues['year'] ? ' selected="selected"' : '';
			$dateOutput .= "<option value=\"".sprintf("%04d", $i)."\" id=\"{$fieldId}_".sprintf("%04d", $i)."\"{$selected}>".sprintf("%04d", $i)."</option>";
		}
		$dateOutput .= "</select>";

		return "<div class=\"subInputWrap\">{$dateOutput}</div>";
	}
}