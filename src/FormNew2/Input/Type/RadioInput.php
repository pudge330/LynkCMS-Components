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
use Lynk\Component\FormNew2\Input\Type\View\RadioView;

/**
 * Color input type.
 */
class RadioInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'radioField';

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new RadioView($this);
	}

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		if ($settings->options->data && !is_array($settings->options->data))
			$settings->options->data = $this->helper->processCsv($settings->options->data, $settings->options->format);
		else if (!$settings->options->data)
			$settings->options->data = [];
		if ($settings->options->dataFile) {
			$settings->options->data = array_merge(
				$settings->options->data
				,$this->helper->processDataFile($settings->options->dataFile, $settings->options->format)
			);
		}
		if (!$settings->options->layout) {
			$settings->options->layout = 'inline';
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
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == '')) {
			return [false, "{$displayName} is required"];
		}
		else if (isset($data[$this->name]) && $data[$this->name]) {
			if (!$this->helper->validateType($this->settings->options->allow, $data[$this->name]))
				return [false, "{$displayName} must be a {$this->settings->options->allow} value"];
			// if (sizeof($this->settings->options->data) > 0 && !$this->helper->validateExists($data[$this->name], $this->settings->options->data))
			// 	return [false, "{$displayName} contains invalid data"];
		}
		return [true];
	}
}