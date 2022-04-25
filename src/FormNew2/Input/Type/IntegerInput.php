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
use Lynk\Component\FormNew2\Input\Type\View\IntegerView;

/**
 * Integer input type.
 */
class IntegerInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'integerField';

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		if ($settings->options->step === null)
			$settings->options->step = '1';
		return $settings;
	}

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new IntegerView($this);
	}

	/**
	 * Validate submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Boolean as first value that indicates whether or not the value was valid.
	 *               Second ootional value describes the error.
	 */
	function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == ''))
			return [false, "{$displayName} is required"];
		else if (isset($data[$this->name]) && $data[$this->name] && !$this->validator->int($data[$this->name], ['min' => $this->settings->options->min, 'max' => $this->settings->options->max])) {
			if ($this->settings->options->min && $this->settings->options->max)
				return [false, "{$displayName} must be a integer between {$this->settings->options->min} and {$this->settings->options->max}"];
			else if ($this->settings->options->min)
				return [false, "{$displayName} must be a integer at least {$this->settings->options->min} or greater"];
			else if ($this->settings->options->max)
				return [false, "{$displayName} must be a integer no greater than {$this->settings->options->max}"];
			else
				return [false, "{$displayName} must be a integer"];
		}
		else
			return [true];
	}
}