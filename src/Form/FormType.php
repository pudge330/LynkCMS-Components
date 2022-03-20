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

namespace Lynk\Component\Form;

use Lynk\Component\Container\StandardContainer;
use Lynk\Component\Container\Container;
use Lynk\Component\Form\Input\Type as InputType;
use Lynk\Component\Form\Input\inputCollection;
use Lynk\Component\Form\Security\FormTokenizer;
use Lynk\Component\Form\Validator\BasicDataValidator;

/**
 * Form type, represents a HTML form.
 */
class FormType {

	/**
	 * @var bool Auto-validate form submissions.
	 */
	protected $autoValidate;

	/**
	 * @var StandardContainer Default form data.
	 */
	protected $defaultData;

	/**
	 * @var Array List of input field settings.
	 */
	protected $fieldSettings;

	/**
	 * @var Array Bound form submission data.
	 */
	protected $formData;

	/**
	 * @var Array List of form errors, instances of FormError.
	 */
	protected $formErrors;

	/**
	 * @var string Form name override.
	 */
	protected $formNameOverride;

	/**
	 * @var string Form method type.
	 */
	protected $formMethod;

	/**
	 * @var StandardContainer Form options.
	 */
	protected $formOptions;

	/**
	 * @var FormTokenizer Form CSRF token class.
	 */
	protected $formTokenizer;

	/**
	 * @var bool Inline form errors, if true renders errors with element.
	 */
	protected $inlineErrors;

	/**
	 * @var Array Form input collection.
	 */
	protected $inputCollection;

	/**
	 * @var bool Is submission data bound.
	 */
	protected $isDataBound;

	/**
	 * @var bool Is submission data processed.
	 */
	protected $isDataProcessed;

	/**
	 * @var bool Is form submitted.
	 */
	protected $isSubmitted;

	/**
	 * @var string Form name format. Either 'array' or 'snake', array by default.
	 */
	protected $nameFormat;

	/**
	 * @var bool Whether or not form data has been validated.
	 */
	protected $ranValidationCheck;

	/**
	 * @var Array Array of registered input type class names.
	 */
	protected $registeredInputTypes;

	/**
	 * @var Container Service container.
	 */
	protected $serviceContainer = null;

	/**
	 * @var bool Use CSRF token.
	 */
	protected $usingToken;

	/**
	 * @var BasicDataValidator Data validator class.
	 */
	protected $validator;

	/**
	 * @param Container Optional. Service container.
	 * @param Array Optional. Form options.
	 * @param Array Optional. Form default values.
	 */
	public function __construct(/* $container, [ $options, $default ]*/) {
		$container = $options = $defaults = null;
		$arguments = func_get_args();
		switch (sizeof($arguments)) {
			case 3:
				$container = $arguments[0];
				$options = $arguments[1];
				$defaults = $arguments[2];
			break;
			case 2:
				if (is_object($arguments[0])) {
					$container = $arguments[0];
					$options = $arguments[1];
				}
				else {
					$options = $arguments[0];
					$defaults = $arguments[1];
				}
			break;
			case 1:
				if (is_object($arguments[0])) {
					$container = $arguments[0];
				}
				else {
					$options = $arguments[0];
				}
			break;
		}

		//--Validate constructor arguments
		if ($container && !($container instanceof Container)) {
			throw new Exception(
				'FormType - invalid arguments passed to constructor. Argument $container is expected to be of type Lynk\\Component\\Container\\Container, ' . get_class($container) . ' given.'
			);
		}
		else if (!$container) {
			//--just to prevent errors if getContainer() is called
			//--and is not checked to be valid container before using
			$container = new Container();
		}
		if ($options && !is_array($options)) {
			$type = is_object($options) ? get_class($options) : gettype($options);
			throw new Exception(
				'FormType - invalid arguments passed to constructor. Argument $options is expected to be an array, ' . $type . ' given.'
			);
		}
		else if (!$options) {
			$options = [];
		}
		if ($defaults && !is_array($defaults)) {
			$type = is_object($defaults) ? get_class($defaults) : gettype($defaults);
			throw new Exception(
				'FormType - invalid arguments passed to constructor. Argument $defaults is expected to be an array, ' . $type . ' given.'
			);
		}
		else if (!$defaults) {
			$defaults = [];
		}

		$this->serviceContainer = $container;
		$this->defaultData = new StandardContainer($defaults);

		//--Built-in form options
		$options = array_merge([
			'_name' => null
			,'_method' => 'GET'
			,'_csrf' => false
			,'_nameFormat' => InputCollection::NAME_FORMAT_ARRAY
			,'_inlineErrors' => false
			,'_validate' => true
			,'_fields' => []
			,'_inputTypes' => []
			,'_secret' => null
			,'_isSubmitted' => false
		], $options);
		$fieldSettings = $csrfSecret = null;
		$this->formNameOverride = $options['_name'];
		$this->formMethod = is_array($options['_method']) ? $options['_method'] : [$options['_method']];
		array_walk($this->formMethod, function(&$val) {
			$val = strtoupper($val);
		});
		$this->usingToken = $options['_csrf'];
		$this->nameFormat = $options['_nameFormat'];
		$this->inlineErrors = $options['_inlineErrors'];
		$this->autoValidate = $options['_validate'];
		$fieldSettings = $options['_fields'];
		$this->registeredInputTypes = array_merge(
			self::defaultInputTypes()
			,$this->registerInputTypes()
			,$options['_inputTypes']
		);
		$csrfSecret = $options['_secret'];
		$this->isSubmitted = $options['_isSubmitted'];
		unset($options['_name'], $options['_method'], $options['_csrf'], $options['_nameFormat'], $options['_inlineErrors'], $options['_validate'], $options['_fields'], $options['_inputTypes'], $options['_secret'], $options['_isSubmitted']);
		$this->formOptions = new StandardContainer($options);

		$this->ranValidationCheck = false;
		$this->formTokenizer = FormTokenizer::create($csrfSecret);
		$this->fieldSettings = $this->inputs = $this->formData = $this->formErrors = [];
		$this->validator = new BasicDataValidator();
		$this->isDataBound = false;
		$this->isDataProcessed = false;
		$this->bindData();
		$initFieldSettings = $this->init();
		$initFieldSettings = $initFieldSettings && is_array($initFieldSettings) ? $initFieldSettings : [];
		$this->fieldSettings = \lynk\deepMerge($this->fieldSettings, $initFieldSettings, $fieldSettings);

		$processedSettings = $this->processFieldSettings($this->fieldSettings);
		if ($processedSettings && is_array($processedSettings)) {
			$this->fieldSettings = $processedSettings;
		}
		$this->inputCollection = new inputCollection($this->fieldSettings, [
			'token' => $this->usingToken,
			'types' => $this->getRegisteredInputTypes(),
			'name' => $this->getName(),
			'format' => $this->nameFormat
		]);

		if ($this->isBound() && $this->autoValidate)
			$this->validate();
	}

	/**
	 * Overridable method used to pre-process field settings before adding to collection.
	 */
	protected function processFieldSettings(Array $settings) { return $settings; }

	/**
	 * Overridable method used to initialize form or manipulate values.
	 */
	protected function init() {}

	/**
	 * Overridable method used to validate data and add form errors before processing.
	 */
	protected function validateForm() {}

	/**
	 * Overridable method used to process a successful form submission.
	 */
	protected function processForm() {}

	/**
	 * Overridable method used to hard-code form name.
	 */
	public function formName() {}

	/**
	 * Overridable method used to register input types or add custom ones.
	 */
	protected function registerInputTypes() { return []; }

	/**
	 * Get form name.
	 * 
	 * @return string Form name.
	 */
	public function getName() {
		return $this->formNameOverride ?: $this->formName();
	}

	/**
	 * Bind submission data to instance. Checks if the HTTP request method is one of the desired methods.
	 * If name format is 'array' the $_FILES structure groups the file submission values together is a nested array,
	 * otherwise each array in $_FILES represents one file submission. The method will internally call processSubmittedFiles()
	 * to normalie the data if the name format is 'array'.
	 */
	public function bindData() {
		$formMethods = $this->formMethod;
		$formName = $this->getName();
		$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
		$isBindable = in_array($requestMethod, $formMethods);
		if ($isBindable) {
			$requestData = in_array($requestMethod, ['GET', 'POST'])
				? $GLOBALS["_{$requestMethod}"]
				: $GLOBALS['_REQUEST'];
			if ($formName) {
				if ($this->nameFormat == 'array' && isset($requestData[$formName])) {
					$this->formData = $requestData[$formName];
					$this->isDataBound = true;
				}
				else {
					$submittedData = [];
					foreach ($requestData as $key => $value) {
						if (preg_match('/^' . preg_quote($formName) . '_(.*)/', $key, $match)) {
							$submittedData[$match[1]] = $value;
						}
					}
					if (sizeof($submittedData)) {
						$this->formData = $submittedData;
						$this->isDataBound = true;
					}
				}
			}
			else {
				if ($this->isSubmitted) {
					$this->formData = $requestData;
					$this->isDataBound = true;
				}
			}
			if ($this->isDataBound) {
				if ($this->nameFormat == 'array' && $formName) {
					if (isset($_FILES[$formName]['error']) && sizeof($_FILES[$formName]['error']) > 0)
						$this->formData = array_merge($this->formData, $this->processSubmittedFiles());
				}
				else {
					$this->formData = array_merge($this->formData, $_FILES);
				}
			}
		}
	}

	/**
	 * Normalize $_FILES data so that each sub-array value represents one file submission.
	 * 
	 * @return Array File submission data.
	 */
	protected function processSubmittedFiles() {
		$files = [];
		$formName = $this->getName();
		foreach ($_FILES[$formName]['error'] as $file => $value) {
			$files[$file] = [
				'name' => isset($_FILES[$formName]['name'][$file]) ? $_FILES[$formName]['name'][$file] : null
				,'type' => isset($_FILES[$formName]['type'][$file]) ? $_FILES[$formName]['type'][$file] : null
				,'tmp_name' => isset($_FILES[$formName]['tmp_name'][$file]) ? $_FILES[$formName]['tmp_name'][$file] : null
				,'error' => $value
				,'size' => isset($_FILES[$formName]['size'][$file]) ? $_FILES[$formName]['size'][$file] : 0
			];
		}
		return $files;
	}

	/**
	 * Check if submission data is bound.
	 * 
	 * @return bool True if data is bound, false otherwise.
	 */
	public function isBound() {
		return $this->isDataBound;
	}

	/**
	 * Built-in form submission validation.
	 */
	public function validate() {
		$data = $this->getSubmittedData();
		if ($this->isBound() && !$this->ranValidationCheck) {
			$this->formErrors = [];
			if ($this->usingToken) {
				if (!isset($data['_token']) || !$data['_token']) {
					$this->addError('_token', 'CSRF Token is missing');
				}
				else if (!$this->formTokenizer->validateToken($this->getName(), $data['_token'])) {
					$this->addError('_token', 'CSRF Token is invalid');
				}
			}
			foreach ($this->inputCollection->all() as $name => $input) {
				$valResult = $input->validateData($data, $name);
				if (!$valResult[0]) {
					$this->addError($name, $valResult[1]);
				}
			}
			$formErrors = $this->validateForm();
			if (is_array($formErrors)) {
				foreach ($formErrors as $key => $error) {
					if (!is_numeric($key))
						$this->addError($key, $name);
					else
						$this->addError($error);
				}
			}
			$this->ranValidationCheck = true;
		}
	}

	/**
	 * Process form submission data.
	 */
	public function process() {
		return $this->processForm();
	}

	/**
	 * Get input collection.
	 * 
	 * @return InputCollection The input collection
	 */
	public function inputs() {
		return $this->inputCollection;
	}

	/**
	 * Get registered input types.
	 * 
	 * @return Array List of registered types.
	 */
	public function getRegisteredInputTypes() {
		return $this->registeredInputTypes;
	}

	/**
	 * Get default form data.
	 * 
	 * @return Array $data Default data.
	 */
	public function getDefaultData() {
		return $this->defaultData->export();
	}

	/**
	 * Set default form data.
	 * 
	 * @param Array $data Default data.
	 */
	public function setDefaultData(Array $default) {
		$this->defaultData = new StandardContainer($default);
	}

	/**
	 * Get submitted form data.
	 * 
	 * @return Array Form data.
	 */
	public function getSubmittedData() {
		return array_merge($this->defaultData->export(), $this->formData);
	}

	/**
	 * Sets form data.
	 * 
	 * @param Array $data Form data.
	 */
	public function setSubmittedData($data) {
		$this->formData = $data;
	}

	/**
	 * Merge submitted data.
	 * 
	 * @param Array $data Data array.
	 * @param bool $after Optional. Merge date after current array, overriding existing values.
	 */
	public function mergeSubmittedData($data, $after = true) {
		if ($after)
			$this->formData = array_merge($this->formData, $data);
		else
			$this->formData = array_merge($data, $this->formData);
	}

	/**
	 * Clear/reset submitted form data.
	 */
	public function clearSubmittedData() {
		$this->formData = [];
	}

	/**
	 * Get form errors.
	 * 
	 * @return Array List of form errors.
	 */
	public function getFormErrors() {
		return $this->formErrors;
	}

	/**
	 * Add form error to erro list.
	 * 
	 * @param mixed $var1 FormError instance, error message or form error key.
	 * @param FormError $var2 FormError instance or error message.
	 */
	public function addError($var1, $var2 = null) {
		if ($var2) {
			if (!($var2 instanceof FormError))
				$var2 = new FormError($var1, $var2);
			$this->formErrors[$var1] = $var2;
		}
		else {
			if (!($var1 instanceof FormError))
				$var1 = new FormError($var1);
			$this->formErrors[] = $var1;
		}
	}

	/**
	 * Add list of form errors.
	 * 
	 * @param Array $errors List of form errors.
	 */
	public function addErrors($errors) {
		foreach ($errors as $key => $val) {
			if (is_numeric($key)) {
				$this->addError($val);
			}
			else {
				$this->addError($key, $val);
			}
		}
	}

	/**
	 * Clean/reset form errors.
	 */
	public function clearErrors() {
		$this->formErrors = [];
	}

	/**
	 * Check if form has errors.
	 * 
	 * @return bool True if errors exist, false if not.
	 */
	public function hasErrors() {
		return (sizeof($this->formErrors) > 0 ? true : false);
	}

	/**
	 * Check if form is valid. Data is bound, validation occurred and form has no errors.
	 * 
	 * @return bool True if is valid, false if not.
	 */
	public function isValid() {
		return ($this->isBound() && $this->ranValidationCheck && !$this->hasErrors());
	}

	/**
	 * Check if form is invalid. Data is bound and validation occurred but form has errors.
	 * 
	 * * @return bool True if invalid, false if not.
	 */
	public function isInvalid() {
		return $this->isValid() ? false : true;
	}

	/**
	 * Check if form has been validated.
	 * 
	 * @return bool True if form data is validated, false otherwise.
	 */
	public function hasValidated() {
		return $this->ranValidationCheck;
	}

	/**
	 * Create form view object.
	 * 
	 * @return FormView Form view instance.
	 */
	public function createView() {
		return new FormView($this);
	}

	/**
	 * Get options for view.
	 * 
	 * @return Array List of view options.
	 */
	public function getViewOptions() {
		return Array(
			'usingToken' => $this->usingToken
			,'inlineErrors' => $this->inlineErrors
			,'nameFormat' => $this->nameFormat
		);
	}

	/**
	 * Get options object.
	 * 
	 * @return StandardContainer Options.
	 */
	public function getOptions() {
		return $this->formOptions;
	}

	/**
	 * Get option value.
	 * 
	 * @param string $key The values key.
	 * 
	 * @return mixed The stored value or null if non-existent..
	 */
	public function getOption($key) {
		return $this->formOptions->get($key);
	}

	/**
	 * Set option value.
	 * 
	 * @param string $key The values key.
	 * @param mixed $value The value to store.
	 */
	public function setOption($key, $value) {
		return $this->formOptions->set($key, $value);
	}

	/**
	 * Check whether or not a option exists.
	 * 
	 * @param string $key The key to check for.
	 * 
	 * @return bool True if key exists, false if not.
	 */
	public function hasOption($key) {
		return $this->formOptions->has($key);
	}

	/**
	 * Get form CSRF tokenizer.
	 * 
	 * @return FormTokenizer Tokenizer instance.
	 */
	public function getTokenizer() {
		return $this->formTokenizer;
	}

	/**
	 * Get service container.
	 * 
	 * @return Container Service container.
	 */
	public function getContainer() {
		return $this->serviceContainer;
	}

	/**
	 * Default input class types.
	 * 
	 * @return Array Input class names.
	 */
	protected static function defaultInputTypes() {
		return [
			'checkbox' => InputType\CheckboxInput::CLASS
			,'color' => InputType\ColorInput::CLASS
			,'date' => InputType\DateInput::CLASS
			,'datetime' => InputType\DatetimeInput::CLASS
			,'double' => InputType\DoubleInput::CLASS
			,'file' => InputType\FileInput::CLASS
			,'hidden' => InputType\HiddenInput::CLASS
			,'int' => InputType\IntegerInput::CLASS
			,'password' => InputType\PasswordInput::CLASS
			,'radio' => InputType\RadioInput::CLASS
			,'range' => InputType\RangeInput::CLASS
			,'selectable_date' => InputType\SelectableDateInput::CLASS
			,'selectable_datetime' => InputType\SelectableDatetimeInput::CLASS
			,'selectable_time' => InputType\SelectableTimeInput::CLASS
			,'select' => InputType\SelectInput::CLASS
			,'textarea' => InputType\TextareaInput::CLASS
			,'text' => InputType\TextInput::CLASS
			,'time' => InputType\TimeInput::CLASS
		];
	}
}