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
use Lynk\Component\FormNew2\Input\Type\View\SelectableTimeView;

/**
 * Textarea input type.
 */
class SelectableTimeInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'selectableTimeField';

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new SelectableTimeView($this);
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
		$min = $min ? Datetime::createFromFormat('Y-m-d H:i', '1970-01-01 ' . $min) : null;
		$max = $max ? Datetime::createFromFormat('Y-m-d H:i', '1970-01-01 ' . $max) : null;

		$hourValue = isset($data["{$this->name}_hour"]) && $data["{$this->name}_hour"] != '' ? $data["{$this->name}_hour"] : null;
		$minuteValue = isset($data["{$this->name}_minute"]) && $data["{$this->name}_minute"] != '' ? $data["{$this->name}_minute"] : null;
		$periodValue = isset($data["{$this->name}_period"]) && $data["{$this->name}_period"] != '' ? $data["{$this->name}_period"] : null;
		$hourInRange = $hourValue && ((int)$hourValue >= 0 && (int)$hourValue <= 12);
		$minuteInRange = $minuteValue && ((int)$minuteValue >= 0 && (int)$minuteValue <= 60);
		$periodInRange = $periodValue && ($periodValue == 'AM' || 'PM');

		$allExists = ($hourValue && $minuteValue && $periodValue);
		$isAllValid = ($hourInRange && $minuteInRange && $periodInRange);

		if ($this->settings->options->required && !$allExists) {
			return [false, "{$displayName} is required"];
		}
		else if ($allExists && !$isAllValid) {
			return [false, "{$displayName} had invalid options"];
		}
		else if ($isAllValid) {
			$submittedObject = Datetime::createFromFormat('Y-m-d h:i A', "1970-01-01 {$hourValue}:{$minuteValue} {$periodValue}");
			$passedMinTest = $passedMaxTest = false;
			if ($min)
				$passedMinTest = ((int)$submittedObject->format('Hi') >= (int)$min->format('Hi'));
			if ($max)
				$passedMaxTest = ((int)$submittedObject->format('Hi') <= (int)$max->format('Hi'));
			$formattedMin = $min ? $min->format('h:i A') : '';
			$formattedMax = $max ? $max->format('h:i A') : '';
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