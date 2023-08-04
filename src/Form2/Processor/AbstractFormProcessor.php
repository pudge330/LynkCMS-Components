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

namespace Lynk\Component\Form2\Processor;

use Exception;
use Lynk\Component\Container\StandardContainer;
use Lynk\Component\FormNew2\FormType;
use Lynk\Component\FormNew2\OptionTrait;

/**
 * Abstract form processor class.
 */
class AbstractFormProcessor {

	/**
	 * @var StandardContainer Form processor options.
	 */
	protected $options;

	/**
	 * @var Array Input processors.
	 */
	protected $processors;

	/**
	 * @param Array $options Optional. Form processor options.
	 */
	public function __construct($options = []) {
		$this->options = new StandardContainer($options);
		$this->processors = $this->inputProcessors();
	}

	/**
	 * Process form submission data.
	 * 
	 * @param Array $data Form submission data.
	 * @param FormType $form Form instance.
	 * 
	 * @return Array Processed form submission data.
	 */
	public function processSubmissionData(&$data, FormType $form) {
		$settings = $form->getFieldSettings();
		if (!is_array($data)) {
			$data = [];
		}
		foreach ($settings as $fieldKey => $fieldSettings) {
			if (array_key_exists($fieldSettings->type, $this->processors)) {
				$processorKey = is_string($this->processors[$fieldSettings->type]) ? $this->processors[$fieldSettings->type] : $fieldSettings->type;
				$data = $this->processors[$processorKey]->processSubmissionData(
					$data
					,$fieldKey
					,$fieldSettings
				);
			}
		}
		return $data;
	}

	/**
	 * Process data for form.
	 * 
	 * @param Array $data Form submission data.
	 * @param FormType $form Form instance.
	 * 
	 * @return Array Processed form data.
	 */
	public function processFormData(&$data, FormType $form) {
		$settings = $form->getFieldSettings();
		if (!is_array($data)) {
			$data = [];
		}
		foreach ($settings as $fieldKey => $fieldSettings) {
			if (array_key_exists($fieldSettings->type, $this->processors)) {
				$processorKey = is_string($this->processors[$fieldSettings->type]) ? $this->processors[$fieldSettings->type] : $fieldSettings->type;
				$data = $this->processors[$processorKey]->processFormData(
					$data
					,$fieldKey
					,$fieldSettings
				);
			}
		}
		return $data;
	}

	/**
	 * Get list of form input processors.
	 * 
	 * @return Array Form input processors.
	 */
	public function inputProcessors() {
		throw new Exception('AbstractProcessor::inputProcessors() must be overwritten');
	}

	/**
	 * Get form field settings.
	 * 
	 * @param FormType Form instance.
	 * 
	 * @return Array Field settings.
	 */
	protected function getFieldSettings(FormType $form) {
		$inputs = $form->inputs()->all();
		$settings = [];
		foreach ($inputs as $key => $input) {
			$settings[$key] = $input->getSettings();
		}
		return $settings;
	}

	/**
	 * Get options object.
	 * 
	 * @return StandardContainer Options.
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Get option value.
	 * 
	 * @param string $key The values key.
	 * 
	 * @return mixed The stored value or null if non-existent..
	 */
	public function getOption($key) {
		return $this->options->get($key);
	}

	/**
	 * Set option value.
	 * 
	 * @param string $key The values key.
	 * @param mixed $value The value to store.
	 */
	public function setOption($key, $value) {
		return $this->options->set($key, $value);
	}

	/**
	 * Check whether or not a option exists.
	 * 
	 * @param string $key The key to check for.
	 * 
	 * @return bool True if key exists, false if not.
	 */
	public function hasOption($key) {
		return $this->options->has($key);
	}
}