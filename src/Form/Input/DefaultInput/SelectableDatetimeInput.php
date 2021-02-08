<?php
namespace BGStudios\Component\Form\Input\DefaultInput;

use Datetime;
use BGStudios\Component\Form\Input\InputType;

class SelectableDatetimeInput extends InputType {
	protected $fieldName = 'selectableDatetimeField';
	public function processData($data) {
		if (!isset($data[$this->name]) || !$data[$this->name])
			$data[$this->name] = null;
		return $data;
	}
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;

		$min = $this->settings->options->min;
		$max = $this->settings->options->max;
		$min = $min ? Datetime::createFromFormat('Y-m-d H:i', $min) : null;
		$max = $max ? Datetime::createFromFormat('Y-m-d H:i', $max) : null;

		$yearValue = isset($data["{$this->name}_year"]) && $data["{$this->name}_year"] != '' ? $data["{$this->name}_year"] : null;
		$monthValue = isset($data["{$this->name}_month"]) && $data["{$this->name}_month"] != '' ? $data["{$this->name}_month"] : null;
		$dayValue = isset($data["{$this->name}_day"]) && $data["{$this->name}_day"] != '' ? $data["{$this->name}_day"] : null;
		$yearInRange = $yearValue && is_numeric($yearValue) && is_int((int)$yearValue);
		$monthInRange = $monthValue && ((int)$monthValue >= 1 && (int)$monthValue <= 12);
		$dayInRange = $dayValue && ((int)$dayValue >= 1 && (int)$dayValue <= 31);

		$hourValue = isset($data["{$this->name}_hour"]) && $data["{$this->name}_hour"] != '' ? $data["{$this->name}_hour"] : null;
		$minuteValue = isset($data["{$this->name}_minute"]) && $data["{$this->name}_minute"] != '' ? $data["{$this->name}_minute"] : null;
		$periodValue = isset($data["{$this->name}_period"]) && $data["{$this->name}_period"] != '' ? $data["{$this->name}_period"] : null;
		$hourInRange = $hourValue && ((int)$hourValue >= 0 && (int)$hourValue <= 12);
		$minuteInRange = $minuteValue && ((int)$minuteValue >= 0 && (int)$minuteValue <= 60);
		$periodInRange = $periodValue && ($periodValue == 'AM' || 'PM');

		$allExists = ($yearValue && $monthValue && $dayValue && $hourValue && $minuteValue && $periodValue);
		$isAllValid = ($yearInRange && $monthInRange && $dayInRange && $hourInRange && $minuteInRange && $periodInRange);

		if ($this->settings->options->required && !$allExists) {
			return [false, "{$displayName} is required"];
		}
		else if ($allExists && !$isAllValid) {
			return [false, "{$displayName} had invalid options"];
		}
		else if ($isAllValid) {
			$submittedObject = Datetime::createFromFormat('Y-m-d h:i A', "{$yearValue}-{$monthValue}-{$dayValue} {$hourValue}:{$minuteValue} {$periodValue}");
			$passedMinTest = $passedMaxTest = false;
			if ($min)
				$passedMinTest = ((int)$submittedObject->format('YmdHi') >= (int)$min->format('YmdHi'));
			if ($max)
				$passedMaxTest = ((int)$submittedObject->format('YmdHi') <= (int)$max->format('YmdHi'));
			$formattedMin = $min ? $min->format('F d, Y h:i A') : '';
			$formattedMax = $max ? $max->format('F d, Y h:i A') : '';
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
	public function processSettings($settings) {
		$min = $settings->options->min ? Datetime::createFromFormat('Y-m-d H:i', $settings->options->min) : null;
		$max = $settings->options->max ? Datetime::createFromFormat('Y-m-d H:i', $settings->options->max) : null;
		$minYear = $min ? $min->format('Y') : 1975;
		$maxYear = $max ? $max->format('Y') : (int)date('Y') + 5;
		if ($settings->options->startYear === null)
			$settings->options->startYear = $minYear;
		if ($settings->options->endYear === null)
			$settings->options->endYear = $maxYear;
		return $settings;
	}
	public function renderLabel(&$attr, $values = [], &$errors = []) {
		return $this->settings->label && !$this->settings->options->noLabel ? $this->settings->label : null;
	}
	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFormFieldClasses($this->settings);

		//--name, type, value(s)
		$submittedValues = array('year' => null, 'month' => null, 'day' => null, 'hour' => null, 'minute' => null, 'second' => null, 'period' => null);
		$submittedValue = $this->helper->getDefaultValues($this->settings, $values, $this->name);
		if ($submittedValue) {
			error_log($submittedValue);
			$submittedDatetime = $this->helper->toDateTime($submittedValue);
			$submittedValues['year'] = $submittedDatetime->format('Y');
			$submittedValues['month'] = $submittedDatetime->format('m');
			$submittedValues['day'] = $submittedDatetime->format('d');
			$submittedValues['hour'] = $submittedDatetime->format('h');
			$submittedValues['minute'] = $submittedDatetime->format('i');
			$submittedValues['period'] = $submittedDatetime->format('A');
		}
		$submittedValues['year'] = isset($values["{$this->name}_year"]) ? $values["{$this->name}_year"] : $submittedValues['year'];
		$submittedValues['month'] = isset($values["{$this->name}_month"]) ? $values["{$this->name}_month"] : $submittedValues['month'];
		$submittedValues['day'] = isset($values["{$this->name}_day"]) ? $values["{$this->name}_day"] : $submittedValues['day'];
		$submittedValues['hour'] = isset($values["{$this->name}_hour"]) ? $values["{$this->name}_hour"] : $submittedValues['hour'];
		$submittedValues['minute'] = isset($values["{$this->name}_minute"]) ? $values["{$this->name}_minute"] : $submittedValues['minute'];
		$submittedValues['period'] = isset($values["{$this->name}_period"]) ? $values["{$this->name}_period"] : $submittedValues['period'];
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
			$dateOutput .= "<option value=\"".sprintf("%04d", $i)."\" id=\"{$fieldId}_{$i}\"{$selected}>{$i}</option>";
		}
		$dateOutput .= "</select>";

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_hour');
		$attr['input']['attr']['id'] = "{$fieldId}_hour";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-hour";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"\" id=\"{$fieldId}_none\">--</option>";
		for ($i = 1; $i <= 12; $i++) {
			$selected = sprintf("%02d", $i) == $submittedValues['hour'] ? ' selected="selected"' : '';
			$dateOutput .= "<option value=\"".sprintf("%02d", $i)."\" id=\"{$fieldId}_".sprintf("%02d", $i)."\"{$selected}>".sprintf("%02d", $i)."</option>";
		}
		$dateOutput .= "</select>";

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_minute');
		$attr['input']['attr']['id'] = "{$fieldId}_minute";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-minute";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"\" id=\"{$fieldId}_none\">--</option>";
		for ($i = 0; $i <= 59; $i++) {
			$selected = sprintf("%02d", $i) == $submittedValues['minute'] ? ' selected="selected"' : '';
			$dateOutput .= "<option value=\"".sprintf("%02d", $i)."\" id=\"{$fieldId}_".sprintf("%02d", $i)."\"{$selected}>".sprintf("%02d", $i)."</option>";
		}
		$dateOutput .= "</select>";

		$attr['input']['attr']['name'] = $this->helper->getFieldSuffixId($this->inputName, '_period');
		$attr['input']['attr']['id'] = "{$fieldId}_period";
		$attr['input']['attr']['class'] = "{$inputClasses} {$classes['subinput']}-period";
		$inputAttr = $this->helper->buildAttributeString($attr['input']['attr']);
		$inputDataAttr = $this->helper->buildAttributeString($attr['input']['dataAttr'], 'data-');
		$amSelected = 'AM' == $submittedValues['period'] ? ' selected="selected"' : '';
		$pmSelected = 'PM' == $submittedValues['period'] ? ' selected="selected"' : '';
		$dateOutput .= "<select{$inputAttr}{$inputDataAttr}>";
		$dateOutput .= "<option value=\"AM\" id=\"{$fieldId}_am\"{$amSelected}>AM</option>";
		$dateOutput .= "<option value=\"PM\" id=\"{$fieldId}_pm\"{$pmSelected}>PM</option>";
		$dateOutput .= "</select>";

		return "<div class=\"subInputWrap\">{$dateOutput}</div>";
	}
}