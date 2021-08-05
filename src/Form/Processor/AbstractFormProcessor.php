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

namespace LynkCMS\Component\Form\Processor;

use Exception;
use LynkCMS\Component\Form\FormType;
use LynkCMS\Component\Form\OptionTrait;

/**
 * Abstract form processor class.
 */
class AbstractFormProcessor {
	use OptionTrait;

	/**
	 * @var Array Input processors.
	 */
	protected $processors;

	/**
	 * @param Array $options Optional. Form processor options.
	 */
	public function __construct($options = []) {
		$this->setOption($options, true);
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
	 * Process form data.
	 * 
	 * @param Array $data Form submission data.
	 * @param FormType $form Form instance.
	 * 
	 * @return Array Processed form data.
	 */
	public function processData(&$data, FormType $form) {
		$settings = $form->getFieldSettings();
		if (!is_array($data)) {
			$data = [];
		}
		foreach ($settings as $fieldKey => $fieldSettings) {
			if (array_key_exists($fieldSettings->type, $this->processors)) {
				$processorKey = is_string($this->processors[$fieldSettings->type]) ? $this->processors[$fieldSettings->type] : $fieldSettings->type;
				$data = $this->processors[$processorKey]->processData(
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
		$inputs = $form->getInputs();
		$settings = [];
		foreach ($inputs as $key => $input) {
			$settings[$key] = $input->getSettings();
		}
		return $settings;
	}
}