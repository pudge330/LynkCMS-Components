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

namespace LynkCMS\Component\Form\Input\Processor;

use Exception;
use LynkCMS\Component\Form\OptionTrait;
use LynkCMS\Component\Form\FormType;

/**
 * Abstract inout processor class.
 */
class AbstractInputProcessor {
	use OptionTrait;

	/**
	 * @param Array $options Processor options.
	 */
	public function __construct(Array $options = []) {
		$this->setOption($options, true);
	}

	/**
	 * Process form submission data.
	 * 
	 * @param Array $data Form submission data.
	 * @param FormType $form Form instance.
	 * 
	 * @return Array Processed form submission data.
	 */
	public function processFormData(&$data, $key, $settings) {
		throw new Exception('AbstractProcessor::processFormData() must be overwritten');
	}

	/**
	 * Process data for form.
	 * 
	 * @param Array $data Form submission data.
	 * @param FormType $form Form instance.
	 * 
	 * @return Array Processed form data.
	 */
	public function processData(&$data, $key, $settings) {
		throw new Exception('AbstractProcessor::processData() must be overwritten');
	}

	/**
	 * Process field settings array. Make sure sub-array settings are using StandardContainer class.
	 * 
	 * @param Array $settings Field settings.
	 * 
	 * @return Array Processed field settings.
	 */
	protected function processFieldSettings($settings) {
		return FormType::processFieldSettings($settings);
	}
}