<?php
namespace BGStudios\Component\Form;

use BGStudios\Component\Container\Container;

use BGStudios\Component\Form\OptionTrait;
use BGStudios\Component\Form\Input\SettingsContainer;
use BGStudios\Component\Form\Security\CSRFToken;
use BGStudios\Component\Form\Security\FormTokenizer;
use BGStudios\Component\Form\Validator\BasicDataValidator;

use BGStudios\Component\Form\Input\DefaultInput;
use BGStudios\Component\Form\Input\InputType;

class FormType {
	use OptionTrait;
	protected $serviceContainer = null;
	protected $formNameOverride;
	protected $formMethod;
	protected $usingToken;
	protected $nameFormat;
	protected $inlineErrors;
	protected $autoValidate;
	protected $registeredTypes;
	protected $formTokenizer;
	protected $inputs;
	protected $validator;
	protected $isDataBound;
	protected $isDataProcessed;
	protected $fieldSettings;
	protected $formErrors;
	protected $formData;
	protected $ranValidationCheck;
	protected $defaultData;
	protected $isSubmitted;
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
		if ($container && !($container instanceof Container)) {
			throw new Exception(
				'FormType - invalid arguments passed to constructor. Argument $container is expected to be of type BGStudios\\Component\\Container\\Container, ' . get_class($container) . ' given.'
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
		if ($options && !is_array($options)) {
			$type = is_object($options) ? get_class($options) : gettype($options);
			throw new Exception(
				'FormType - invalid arguments passed to constructor. Argument $defaults is expected to be an array, ' . $type . ' given.'
			);
		}
		else if (!$defaults) {
			$defaults = [];
		}

		$this->serviceContainer = $container;
		$this->defaultData = $defaults;

		$options = array_merge([
			'_name' => null
			,'_method' => 'GET'
			,'_csrf' => false
			,'_nameFormat' => 'array'
			,'_inlineErrors' => false
			,'_validate' => true
			,'_fields' => []
			,'_inputTypes' => []
			,'_secret' => null
			,'_isSubmitted' => false
		], $options);
		$this->isSubmitted = $options['_isSubmitted'];
		$this->formNameOverride = $options['_name'];
		$this->formMethod = is_array($options['_method']) ? $options['_method'] : Array($options['_method']);
		array_walk($this->formMethod, function(&$val) {
			$val = strtoupper($val);
		});
		$this->usingToken = $options['_csrf'];
		$this->nameFormat = $options['_nameFormat'];
		$this->inlineErrors = $options['_inlineErrors'];
		$this->autoValidate = $options['_validate'];
		$fieldSettings = $options['_fields'];
		$this->registeredTypes = array_merge(
			$this->inputTypes()
			,$options['_inputTypes']
		);
		$csrfSecret = $options['_secret'];
		unset($options['_name'], $options['_method'], $options['_csrf'], $options['_nameFormat'], $options['_inlineErrors'], $options['_validate'], $options['_fields'], $options['_inputTypes'], $options['_secret']);
		$this->ranValidationCheck = false;
		$this->formTokenizer = FormTokenizer::create($csrfSecret);		
		$this->setOption($options, true);
		$this->fieldSettings = $this->inputs = $this->formData = $this->formErrors = array();
		$this->validator = new BasicDataValidator();
		$this->isDataBound = false;
		$this->isDataProcessed = false;
		$this->bindData();
		$initFieldSettings = $this->init();
		if ($initFieldSettings && is_array($initFieldSettings))
			$fieldSettings = \bgs\deepMerge($fieldSettings, $this->fieldSettings, $initFieldSettings);
		$this->setInputs($this->fieldSettings);
		if ($this->isBound() && $this->autoValidate)
			$this->validate();
	}
	protected function init() {}
	protected function validateData() {}
	protected function processData() {}
	public function getName() {
		return $this->formNameOverride ?: $this->formName();
	}
	public function formName() {}
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
			foreach ($this->inputs as $name => $input) {
				$valResult = $input->validateData($data, $name);
				if (!$valResult[0]) {
					$this->addError($name, $valResult[1]);
				}
			}
			$typeVal = $this->validateData();
			if (is_array($typeVal)) {
				foreach ($typeVal as $key => $error) {
					if (!is_numeric($key))
						$this->addError($key, $name);
					else
						$this->addError($error);
				}
			}
			$this->ranValidationCheck = true;
		}
	}
	public function process() {
		return $this->processData();
	}
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
	public function isBound() {
		return $this->isDataBound;
	}
	protected function processSubmittedFiles() {
		$files = array();
		$formName = $this->getName();
		foreach ($_FILES[$formName]['error'] as $file => $value) {
			$files[$file] = array(
				'name' => isset($_FILES[$formName]['name'][$file]) ? $_FILES[$formName]['name'][$file] : null
				,'type' => isset($_FILES[$formName]['type'][$file]) ? $_FILES[$formName]['type'][$file] : null
				,'tmp_name' => isset($_FILES[$formName]['tmp_name'][$file]) ? $_FILES[$formName]['tmp_name'][$file] : null
				,'error' => $value
				,'size' => isset($_FILES[$formName]['size'][$file]) ? $_FILES[$formName]['size'][$file] : 0
			);
		}
		return $files;
	}
	public function addInput($name, $settings) {
		$this->inputs[$name] = $this->processSettings(Array($name => $settings))[$name];
		return $this;
	}
	public function setInputs(Array $settings) {
		if (!array_key_exists('_token', $settings) && $this->usingToken) {
			if (is_object(array_values($settings)[0])) {
				$settings->_token = new SettingsContainer();
				$settings->_token->options = new SettingsContainer();
				$settings->_token->type = 'hidden';
			}
			else {
				$settings['_token'] = Array(
					'type' => 'hidden'
				);
			}
		}
		$this->inputs = $this->processSettings($settings);
		return $this;
	}
	public function getInput($name) {
		return ($this->hasInput($name) ? $this->inputs[$name] : null);
	}
	public function getInputs() {
		return $this->inputs;
	}
	public function hasInput($name) {
		return array_key_exists($name, $this->inputs);
	}
	public function hasInputs() {
		return (sizeof($this->inputs) > 0);
	}
	public function removeInput($name) {
		unset($this->inputs[$name]);
	}
	public function getRegisteredTypes() {
		return $this->registeredTypes;
	}
	public function getFieldSettings() {
		$inputs = $this->getInputs();
		$settings = [];
		foreach ($inputs as $key => $input) {
			$settings[$key] = $input->getSettings();
		}
		return $settings;
	}
	static public function processFieldSettings(Array $settings) {
		foreach ($settings as $key => $values) {
			if (!is_object($values)) {
				$values = new SettingsContainer($values);
				$values->options = new SettingsContainer($values->options ? $values->options : []);
				$settings[$key] = $values;
			}
		}
		return $settings;
	}
	public function getFieldName($name) {
		$formName = $this->getName();
		$fieldName = $name;
		if ($formName)
			$fieldName = $this->nameFormat == 'snake' ? "{$formName}_{$name}" : "{$formName}[{$name}]";
		return $fieldName;
	}
	public function processSettings($settings) {
		$formName = $this->getName();
		$types = $this->registeredTypes;
		$settings = self::processFieldSettings($settings);
		foreach ($settings as $key => $values) {
			if (!is_object($values)) {
				$values = new SettingsContainer($values);
				$values->options = new SettingsContainer($values->options ? $values->options : []);
				$settings[$key] = $values;
			}
		}
		$inputs = Array();
		foreach ($settings as $key => $values) {
			$fieldName = $this->getFieldName($key);
			if (isset($types[$values->type])) {
				$values = new $types[$values->type]($key, $fieldName, $values);	
			}
			else {
				$values = new $values->type($key, $fieldName, $values);
			}
			$settings[$key] = $values;
		}
		return $settings;
	}
	public function setDefaultData($default) {
		$this->mergeSubmittedData($default, false);
	}
	public function mergeSubmittedData($data, $after = true) {
		if ($after)
			$this->formData = array_merge($this->formData, $data);
		else
			$this->formData = array_merge($data, $this->formData);
	}
	public function getSubmittedData() {
		return array_merge($this->defaultData, $this->formData);
	}
	public function setSubmittedData($data) {
		$this->formData = $data;
	}
	public function clearSubmittedData() {
		$this->formData = [];
	}
	public function formErrors() {
		return $this->formErrors;
	}
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
	public function clearErrors() {
		$this->formErrors = array();
	}
	public function hasErrors() {
		return (sizeof($this->formErrors) > 0 ? true : false);
	}
	public function isValid() {
		return ($this->isBound() && $this->ranValidationCheck && !$this->hasErrors());
	}
	public function isInvalid() {
		return ($this->isBound() && $this->ranValidationCheck && $this->hasErrors());
	}
	public function hasValidated() {
		return $this->ranValidationCheck;
	}
	public function createView() {
		return new FormView($this);
	}
	public function getViewOptions() {
		return Array(
			'usingToken' => $this->usingToken
			,'inlineErrors' => $this->inlineErrors
			,'nameFormat' => $this->nameFormat
		);
	}
	public function getTokenizer() {
		return $this->formTokenizer;
	}
	protected function inputTypes() {
		return array(
			'checkbox' => DefaultInput\CheckboxInput::CLASS
			,'color' => DefaultInput\ColorInput::CLASS
			,'date' => DefaultInput\DateInput::CLASS
			,'datetime' => DefaultInput\DateAndTimeInput::CLASS
			,'datetimelocal' => DefaultInput\DatetimeLocalInput::CLASS
			,'double' => DefaultInput\DoubleInput::CLASS
			,'file' => DefaultInput\FileInput::CLASS
			,'hidden' => DefaultInput\HiddenInput::CLASS
			,'int' => DefaultInput\IntegerInput::CLASS
			,'password' => DefaultInput\PasswordInput::CLASS
			,'radio' => DefaultInput\RadioInput::CLASS
			,'range' => DefaultInput\RangeInput::CLASS
			,'selectable_date' => DefaultInput\SelectableDateInput::CLASS
			,'selectable_datetime' => DefaultInput\SelectableDatetimeInput::CLASS
			,'selectable_time' => DefaultInput\SelectableTimeInput::CLASS
			,'select' => DefaultInput\SelectInput::CLASS
			,'textarea' => DefaultInput\TextareaInput::CLASS
			,'text' => DefaultInput\TextInput::CLASS
			,'time' => DefaultInput\TimeInput::CLASS
		);
	}
	// public static function setServiceContainer($container) {
	// 	static::$serviceContainer = $container;
	// 	InputType::setServiceContainer($container);
	// }
	// public static function getServiceContainer() {
	// 	return static::$serviceContainer;
	// }
	public function getContainer() {
		return $this->serviceContainer;
	}
}