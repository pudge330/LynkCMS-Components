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
use Lynk\Component\FormNew2\Input\Type\View\SelectableDateView;

/**
 * Textarea input type.
 */
class SelectableDateInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'selectableDateField';

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new SelectableDateView($this);
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
		$maxYear = $max ? $max->format('Y') : (int)date('Y') + 10;
		if ($settings->options->startYear === null)
			$settings->options->startYear = $minYear;
		if ($settings->options->endYear === null)
			$settings->options->endYear = $maxYear;
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
}