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

namespace Lynk\Component\FormNew2\Input\Type;

use Lynk\Component\FormNew2\Input\InputType;
use Lynk\Component\FormNew2\Input\Type\View\SelectableDatetimeView;

/**
 * Textarea input type.
 */
class SelectableDatetimeInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'selectableDatetimeField';

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new SelectableDatetimeView($this);
	}

	/**
	 * Process submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Data values.
	 */
	public function processData($data) {
		if (!isset($data[$this->name]) || !$data[$this->name])
			$data[$this->name] = null;
		return $data;
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

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
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
}