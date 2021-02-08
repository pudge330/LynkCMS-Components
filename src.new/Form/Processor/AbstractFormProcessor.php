<?php
namespace LynkCMS\Component\Form\Processor;

use Exception;
use LynkCMS\Component\Form\OptionTrait;
use LynkCMS\Component\Form\FormType;

class AbstractFormProcessor {
	use OptionTrait;
	protected $processors;
	public function __construct($options = []) {
		$this->setOption($options, true);
		$this->processors = $this->inputProcessors();
	}
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
	public function inputProcessors() {
		throw new Exception('AbstractProcessor::processors() must be overwritten');
	}
	protected function getFieldSettings(FormType $form) {
		$inputs = $form->getInputs();
		$settings = [];
		foreach ($inputs as $key => $input) {
			$settings[$key] = $input->getSettings();
		}
		return $settings;
	}
}