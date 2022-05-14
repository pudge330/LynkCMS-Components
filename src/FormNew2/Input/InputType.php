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

namespace Lynk\Component\FormNew2\Input;

use Lynk\Component\Container\StandardContainer;
use Lynk\Component\FormNew2\Input\InputHelper;
use Lynk\Component\Util\DataValidator;

/**
 * Form input type class.
 */
class InputType {

	/**
	 * @var Lynk\Component\Container\Container Service container.
	 */
	protected static $serviceContainer = null;

	/**
	 * @var string Input key
	 */
	protected $name;

	/**
	 * @var string Input name.
	 */
	protected $inputName;

	/**
	 * @var StandardContainer Input settings.
	 */
	protected $settings;

	/**
	 * @var mixed Input value.
	 */
	protected $validator;

	/**
	 * @var DataValidator Data validator class.
	 */
	protected $helper;

	/**
	 * @var string Field type name.
	 */
	protected $fieldName = 'textField';

	/**
	 * @var InputView Instance or child of InputView class.
	 */
	protected $inputView;

	/**
	 * @param string $name Field key.
	 * @param string $inputName Field name attribute.
	 * @param StandardContainer $settings Field settings.
	 */
	public function __construct($name, $inputName, StandardContainer $settings) {
		$this->name = $name;
		$this->inputName = $inputName;
		$this->validator = new DataValidator();
		$this->helper = new InputHelper($this->validator);
		$this->settings = $settings;
		$this->settings = $this->processSettings($settings);
		$this->inputView = $this->createView();
	}

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new InputView($this);
	}

	/**
	 * Render input.
	 * 
	 * @param array $values Optional. Submitted or default values.
	 * @param array &$errors Optional. Error messages.
	 * 
	 * @return string Rendered input html
	 */
	public function render($values = [], &$errors = []) {
		return $this->inputView->render($values, $errors);
	}

	/**
	 * Get view class.
	 * 
	 * @return InputView View instance
	 */
	public function getView() {
		return $this->inputView;
	}

	/**
	 * Get helper class.
	 * 
	 * @return InputHelper Instance of InputHelper class
	 */
	public function getHelper() {
		return $this->helper;
	}

	/**
	 * Get validator class.
	 * 
	 * @return DataValidator Instance of DataValidator class
	 */
	public function getValidator() {
		return $this->validator;
	}

	/**
	 * Get field name.
	 * 
	 * @return string Field name
	 */
	public function getFieldName() {
		return $this->fieldName;
	}

	/**
	 * Get name.
	 * 
	 * @return string Name/key
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get input name.
	 * 
	 * @return string Input name
	 */
	public function getInputName() {
		return $this->inputName;
	}

	/**
	 * Process submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Data values.
	 */
	public function processData(Array $data) {
		return $data;
	}

	/**
	 * Validate submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array An array of input errors. Enpty array or null indicates successful data validation.
	 */
	public function validateData($data) {
		return null;
	}

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	protected function processSettings($settings) {
		return $settings;
	}

	/**
	 * Get setting.
	 * 
	 * @param string $name Setting name.
	 * 
	 * @return mixed Setting value.
	 */
	public function getSetting($name) {
		return $this->settings->get($name);
	}

	/**
	 * Set setting.
	 * 
	 * @param string $name Setting name.
	 * @param mixed $setting Setting value
	 */
	public function setSetting($name, $setting) {
		$this->settings->set($name, $setting);
	}

	/**
	 * Check if setting exists.
	 * 
	 * @param string $name Setting name.
	 * 
	 * @return bool True if setting exists, false otherwise.
	 */
	public function hasSetting($name) {
		return $this->settings->has($name);
	}

	/**
	 * Get all settings.
	 * 
	 * @return StandardContainer Settings container object.
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * Get option.
	 * 
	 * @param string $name Option name.
	 * 
	 * @return mixed Option value.
	 */
	public function getOption($name) {
		return $this->settings->options->get($name);
	}

	/**
	 * Set option.
	 * 
	 * @param string $name Option name.
	 * @param mixed $option Option value.
	 */
	public function setOption($name, $option) {
		$this->settings->options->set($name, $option);
	}

	/**
	 * Check if option exists.
	 * 
	 * @param string $name Option name.
	 * 
	 * @return bool True if option exists, false otherwise.
	 */
	public function hasOption($name) {
		return $this->settings->options->has($name);
	}

	/**
	 * Get all options.
	 * 
	 * @return StandardContainer Options container object.
	 */
	public function getOptions() {
		return $this->settings->options;
	}

	/**
	 * Get service container.
	 * 
	 * @return Lynk\Component\Container\Container Service container.
	 */
	public function getContainer() {
		return static::getServiceContainer();
	}

	/**
	 * Set service container.
	 * 
	 * @param Lynk\Component\Container\Container Service container.
	 */
	public static function setServiceContainer($container) {
		static::$serviceContainer = $container;
	}

	/**
	 * Get service container.
	 * 
	 * @return Lynk\Component\Container\Container Service container.
	 */
	public static function getServiceContainer() {
		return static::$serviceContainer;
	}
}