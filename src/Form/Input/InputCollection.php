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

namespace Lynk\Component\Form\Input;

use Lynk\Component\Container\StandardContainer;
use Lynk\Component\Form\Input\Type as InputType;

/**
 * Form input collection class.
 */
class InputCollection {

	const NAME_FORMAT_SNAKE = 'snake';
	const NAME_FORMAT_ARRAY = 'array';

	/**
	 * @var Array Input list.
	 */
	protected $inputs;

	/**
	 * @var StandardContainer Collection settings.
	 */
	protected $settings;

	/**
	 * @param Array $inputs List if input settings or input instances.
	 * @param Array $settings List of configuration options.
	 * 		  token:  bool   Using CSRF token.
	 * 		  types:  Array  List registered input type class names.
	 * 		  name:   string Form name.
	 * 		  format: string Input name format
	 */
	public function __construct(Array $inputs, Array $settings = []) {
		$this->settings = new StandardContainer(array_merge([
			'token' => false,
			'types' => [],
			'name' => null,
			'format' => self::NAME_FORMAT_SNAKE
		], $settings));
		$this->set($inputs);
	}

	/**
	 * Get settings object.
	 * 
	 * @return StandardContainer Options.
	 */
	public function getSettings() {
		return $this->formOptions;
	}

	/**
	 * Get setting value.
	 * 
	 * @param string $key The values key.
	 * 
	 * @return mixed The stored value or null if non-existent.
	 */
	public function getSetting($key) {
		return $this->formOptions->get($key);
	}

	/**
	 * Set setting value.
	 * 
	 * @param string $key The values key.
	 * @param mixed $value The value to store.
	 */
	public function setSetting($key, $value) {
		return $this->formOptions->set($key, $value);
	}

	/**
	 * Set form input fields, process settings and add _token field if CSRF token is required and missing.
	 * 
	 * @param Array $settings List of input field settings.
	 * 
	 * @return FormType This instance.
	 */
	public function set(Array $inputs) {
		if (!array_key_exists('_token', $inputs) && $this->settings->get('token')) {
			$inputs['_token'] = [
				'type' => 'hidden'
			];
		}
		$this->inputs = $this->convertSettingsToInputs($inputs);
		return $this;
	}

	/**
	 * Add form input and process settings.
	 * 
	 * @param string $name Input field name.
	 * @param Array $settings Input field settings.
	 * 
	 * @return FormType This instance.
	 */
	public function add($name, $settings) {
		$this->inputs[$name] = $this->convertSettingsToInputs([$name => $settings])[$name];
		return $this;
	}

	/**
	 * Get field input.
	 * 
	 * @param string $name Input name.
	 * 
	 * @return InputType field input type or null.
	 */
	public function get($name) {
		return ($this->has($name) ? $this->inputs[$name] : null);
	}

	/**
	 * Get all field inputs.
	 * 
	 * @return Array Field input list.
	 */
	public function all() {
		return $this->inputs;
	}

	/**
	 * Check if input exists.
	 * 
	 * @param string $name Input name.
	 * 
	 * @return bool True if input exists, false otherwise.
	 */
	public function has($name) {
		return array_key_exists($name, $this->inputs);
	}

	/**
	 * Check if form has any inputs set.
	 * 
	 * @return bool True if inputs exists, false otherwise.
	 */
	public function hasInputs() {
		return (sizeof($this->inputs) > 0);
	}

	/**
	 * Remove field input.
	 * 
	 * @param string $name Input name.
	 */
	public function remove($name) {
		unset($this->inputs[$name]);
	}

	/**
	 * Get field name attribute for element.
	 * 
	 * @param string $name Field name/key.
	 * 
	 * @return string Element name attribute.
	 */
	public function getFieldName($name) {
		$formName = $this->settings->get('name');
		if ($formName)
			$name = $this->settings->get('format') == 'snake' ? "{$formName}_{$name}" : "{$formName}[{$name}]";
		return $name;
	}

	/**
	 * Get field settings.
	 * 
	 * @return Array List of field settings.
	 */
	public function getFieldSettings() {
		$inputs = $this->all();
		$settings = [];
		foreach ($inputs as $key => $input) {
			$settings[$key] = $input->getSettings();
		}
		return $settings;
	}

	/**
	 * Convert field settings into field input instances.
	 * 
	 * @param Array $settings Field settings.
	 * 
	 * @return Array Input class instances.
	 */
	public function convertSettingsToInputs(Array $settings) {
		$types = $this->settings->get('types');
		$settings = self::processFieldSettings($settings);
		$inputs = [];
		foreach ($settings as $key => $values) {
			$fieldName = $this->getFieldName($key);
			if (!($values instanceof InputType)) {
				if (isset($types[$values->type])) {
					$values = new $types[$values->type]($key, $fieldName, $values);	
				}
				else {
					$values = new $values->type($key, $fieldName, $values);
				}
			}
			$settings[$key] = $values;
		}
		return $settings;
	}

	/**
	 * Process field settings array. Make sure sub-array settings are using StandardContainer class.
	 * 
	 * @param Array $settings Field settings.
	 * 
	 * @return Array Processed field settings.
	 */
	public static function processFieldSettings(Array $settings) {
		foreach ($settings as $key => $values) {
			if (!is_object($values)) {
				$values = new StandardContainer($values);
				$values->options = new StandardContainer($values->options ? $values->options : []);
				$settings[$key] = $values;
			}
		}
		return $settings;
	}
}