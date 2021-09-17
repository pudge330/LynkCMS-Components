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

namespace LynkCMS\Component\Form;

use LynkCMS\Component\Container\StandardContainer;

/**
 * Form view class used to render fields and errors.
 */
class FormView {

	/**
	 * @var Array Submitted data.
	 */
	protected $data;

	/**
	 * @var Array Form errors.
	 */
	protected $errors;

	/**
	 * @var FormType Form instance.
	 */
	protected $form;

	/**
	 * @var Array List of input elements.
	 */
	protected $inputs;

	/**
	 * @var Array List of already outputted fields.
	 */
	protected $outputtedFields;

	/**
	 * @var StandardContainer View options.
	 */
	protected $viewOptions;

	/**
	 * @param FormType Form instance.
	 */
	public function __construct(FormType $form) {
		$this->form = $form;
		$this->inputs = $form->getInputs();
		$this->data = $form->getSubmittedData();
		$this->errors = $form->getFormErrors();
		$this->viewOptions = new StandardContainer($form->getViewOptions());
		$this->resetRemainingFields();
	}

	/**
	 * Get input field.
	 * change: same as getField, only need one.
	 * 
	 * @param string $name Input field name.
	 * 
	 * @return InputType Input field.
	 */
	public function getField($name) {
		if ($this->hasField($name)) {
			return $this->inputs[$name];
		}
	}

	/**
	 * Get list of input fields.
	 * 
	 * @return Array Input list.
	 */
	public function getAllFields() {
		return $this->inputs;
	}

	/**
	 * Check if form has particular field.
	 * 
	 * @param string $name Field name.
	 * 
	 * @return bool True if field exists, false if not.
	 */
	public function hasField($name) {
		return array_key_exists($name, $this->inputs);
	}

	/**
	 * Get list of all field keys.
	 * 
	 * @return Array All field keys.
	 */
	public function getAllFieldKeys() {
		return array_keys($this->inputs);
	}

	/**
	 * Register new CSRF token.
	 * 
	 * @param string $formName Optional. Form name, uses current instance by default.
	 * 
	 * @return string New token.
	 */
	public function registerToken($formName = null) {
		$formName = $formName ?: $this->form->getName();
		return $this->form->getTokenizer()->registerToken($formName);
	}

	/**
	 * Reset remaining fields array.
	 */
	public function resetRemainingFields() {
		$this->outputtedFields = Array();
	}

	/**
	 * Get form errors.
	 * 
	 * @return Array List of form errors.
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Check if form has errors.
	 * 
	 * @return bool True if errors exist, false if not.
	 */
	public function hasErrors() {
		return (sizeof($this->errors) > 0);
	}

	/**
	 * Get rendered form errors as array.
	 * 
	 * @return Array Rendered form errors.
	 */
	public function getErrorHtmlArray() {
		$data = Array();
		foreach ($this->errors as $eKey => $eValue) {
			$data[$eKey] = $eValue->render();
		}
		return $data;
	}

	/**
	 * Render specific form error.
	 * 
	 * @param string $form Form error name.
	 * @param string $type Optional. Render type.
	 * 
	 * @return string Rendered error.
	 */
	public function renderFormError($name, $type = null) {
		if (isset($this->errors[$name])) {
			return $this->errors[$name]->render($type);
		}
	}

	/**
	 * Render all form errors.
	 * 
	 * @param Optional. Render type, 'list' is defaault.
	 * @param Optional. Ordered list type, '1' is default.
	 * 
	 * * @return string Rendered errors.
	 */
	public function renderFormErrors() {
		$args = func_get_args();
		$type = array_pop($args);
		$type = $type ?: 'list';
		$output = '';
		foreach ($this->errors as $key => $error) {
			$output .= "\t" . $error->render($type) . "\n";
		}
		$formNameClass = $this->form->getName() ? " formErrors_{$this->form->getName()}" : '';
		switch ($type) {
			case 'list':
			case 'ul-list':
				if ($output != '')
					$output = "<ul class=\"formErrors{$formNameClass}\">\n{$output}</ul>";
			break;
			case 'ol-list':
				$olType = array_pop($args);
				$olType = $olType ?: '1';
				if ($output != '')
					$output = "<ol class=\"formErrors{$formNameClass}\" type=\"{$olType}\">\n{$output}</ol>";
			break;
			default:
				$output = "<{$type} class=\"formErrors{$formNameClass}\">\n{$output}</{$type}>";
			break;
		}
		return $output;
	}

	/**
	 * Render field.
	 * 
	 * @param string $name Input name.
	 * 
	 * @return string Rendered input.
	 */
	public function renderField($name) {
		$output = '';
		if ($this->hasField($name)) {
			$isToken = ($name == '_token');
			if ($isToken && $this->viewOptions->get('usingToken')) {
				$this->inputs[$name]->setOption('default', $this->registerToken());
			}
			$errors = $this->viewOptions->get('inlineErrors') && !$isToken ? $this->errors : Array();
			$output = $this->inputs[$name]->outputHtml(!$isToken ? $this->data : array(), $errors);
			$this->outputtedFields[] = $name;
		}
		else {
			$output = "Form field '{$name}' is unregistered";
		}
		return $output;
	}

	/**
	 * Render all fields.
	 * change: add option to return array of render fields with input name as key.
	 * 
	 * @param bool $echo Output fields.
	 * 
	 * @return mixed Rendered fields or null if outputted.
	 */
	public function renderAllFields($echo = true) {
		$output = '';
		foreach (array_keys($this->inputs) as $name) {
			if ($echo)
				echo $this->renderField($name);
			else
				$output .= $this->renderField($name);
			$this->outputtedFields[] = $name;
		}
		if (!$echo)
			return $output;
	}

	/**
	 * Render all remaining unrendered fields.
	 * change: add option to return array of render fields with input name as key.
	 * 
	 * @param bool $echo Output fields.
	 * 
	 * @return mixed Rendered fields or null if outputted.
	 */
	public function renderRemainingFields($echo = true) {
		$output = '';
		foreach (array_keys($this->inputs) as $name) {
			if (!in_array($name, $this->outputtedFields)) {
				if ($echo)
					echo $this->renderField($name);
				else
					$output .= $this->renderField($name);
				$this->outputtedFields[] = $name;
			}
		}
		if (!$echo)
			return $output;
	}
}