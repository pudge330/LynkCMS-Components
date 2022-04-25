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
use Lynk\Component\FormNew2\Input\Type\View\DoubleView;

/**
 * Double input type.
 */
class DoubleInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'doubleField';

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		if ($settings->options->step === null)
			$settings->options->step = '0.01';
		return $settings;
	}

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new DoubleView($this);
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
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == ''))
			return [false, "{$displayName} is required"];
		else if (isset($data[$this->name]) && $data[$this->name] && !$this->validator->double($data[$this->name], ['min' => $this->settings->options->min, 'max' => $this->settings->options->max])) {
			if ($this->settings->options->min && $this->settings->options->max)
				return [false, "{$displayName} must be a number between ".number_format($this->settings->options->min, 1, '.', ',')." and ".number_format($this->settings->options->max, 1, '.', ',')];
			else if ($this->settings->options->min)
				return [false, "{$displayName} must be a number at least ".number_format($this->settings->options->min, 1, '.', ',')." or greater"];
			else if ($this->settings->options->max)
				return [false, "{$displayName} must be a number no greater than ".number_format($this->settings->options->max, 1, '.', ',').""];
			else
				return [false, "{$displayName} must be a number"];
		}
		else
			return [true];
	}
}