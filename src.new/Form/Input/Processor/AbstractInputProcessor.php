<?php
namespace LynkCMS\Component\Form\Input\Processor;

use Exception;
use LynkCMS\Component\Form\OptionTrait;
use LynkCMS\Component\Form\FormType;

class AbstractInputProcessor {
	use OptionTrait;
	public function __construct($options = []) {
		$this->setOption($options, true);
	}
	public function processFormData(&$data, $key, $settings) {
		throw new Exception('AbstractProcessor::processFormData() must be overwritten');
	}
	public function processData(&$data, $key, $settings) {
		throw new Exception('AbstractProcessor::processData() must be overwritten');
	}
	protected function processFieldSettings($settings) {
		return FormType::processFieldSettings($settings);
	}
}