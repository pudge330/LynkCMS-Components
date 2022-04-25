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

namespace Lynk\Component\FormNew2\Input\Processor;

use Exception;
use Lynk\Component\Container\StandardContainer;
use Lynk\Component\FormNew\FormType;

/**
 * Abstract inout processor class.
 */
class AbstractInputProcessor {

	/**
	 * @var StandardContainer Form processor options.
	 */
	protected $options;

	/**
	 * @param Array $options Processor options.
	 */
	public function __construct(Array $options = []) {
		$this->options = new StandardContainer($options);
	}

	/**
	 * Process form submission data.
	 * 
	 * @param Array $data Form submission data.
	 * @param FormType $form Form instance.
	 * 
	 * @return Array Processed form submission data.
	 */
	public function processSubmissionData(&$data, $key, $settings) {
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
	public function processFormData(&$data, $key, $settings) {
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