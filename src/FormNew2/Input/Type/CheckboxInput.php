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
use Lynk\Component\FormNew2\Input\Type\View\CheckboxView;

/**
 * Color input type.
 */
class CheckboxInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'checkboxField';

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new CheckboxView($this);
	}

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		if ($settings->options->data && !is_array($settings->options->data)) {
			$settings->options->data = $this->helper->processCsv($settings->options->data, $settings->options->dataformat);
		}
		else if (!$settings->options->data){
			$settings->options->data = [];
		}
		if ($settings->options->dataFile) {
			$settings->options->data = $this->helper->processDataFile($settings->options->dataFile, $settings->options->dataFileFormat) + $settings->options->data;
		}
		if (!$settings->options->layout) {
			$settings->options->layout = 'inline';
		}
		return $settings;
	}

	/**
	 * Process submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Data values.
	 */
	public function processData($data) {
		if (!$this->helper->validateExists($this->name, $data)) {
			$data[$this->name] = null;
		}
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
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || $data[$this->name] == '' || (is_array($data[$this->name]) && sizeof($data[$this->name]) == 0))) {
			return [false, "{$displayName} is required"];
		}
		else if (isset($data[$this->name]) && $data[$this->name]) {
			if (is_array($data[$this->name])) {
				for ($i = 0; $i < sizeof($data[$this->name]); $i++) {
					if (!$this->helper->validateType($this->settings->options->allow, $data[$this->name][$i])) {
						return [false, "{$displayName} must be one or more {$this->settings->options->allow} values"];
					}
				}
			}
			else {
				if (!$this->helper->validateType($this->settings->options->allow, $data[$this->name])) {
					return [false, "{$displayName} must be a {$this->settings->options->allow} value"];
				}
			}
		}
		return [true];
	}
}