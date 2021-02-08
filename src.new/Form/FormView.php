<?php
namespace LynkCMS\Component\Form;

use LynkCMS\Component\Form\Security\FormTokenizer;

class FormView {
	use OptionTrait;
	protected $form;
	protected $inputs;
	protected $data;
	protected $errors;
	protected $outputtedFields;
	public function __construct(FormType $form) {
		$this->form = $form;
		$this->inputs = $form->getInputs();
		$this->data = $form->getSubmittedData();
		$this->errors = $form->formErrors();
		$this->setOption($form->getViewOptions(), true);
		$this->resetRemainingFields();
	}
	public function getField($name) {
		if ($this->hasField($name)) {
			return $this->inputs[$name];
		}
	}
	public function hasField($name) {
		return array_key_exists($name, $this->inputs);
	}
	public function registerToken($formName = null) {
		$formName = $formName ?: $this->form->getName();
		return $this->form->getTokenizer()->registerToken($formName);
	}
	public function getAllFieldKeys() {
		return array_keys($this->inputs);
	}
	public function getInputs() {
		return $this->inputs;
	}
	public function getInput($name) {
		if ($this->hasField($name))
			return $this->inputs[$name];
	}
	public function renderField($name) {
		$output = '';
		if ($this->hasField($name)) {
			$isToken = ($name == '_token');
			if ($isToken && $this->getOption('usingToken')) {
				$this->inputs[$name]->setOption('default', $this->registerToken());
			}
			$errors = $this->getOption('inlineErrors') && !$isToken ? $this->errors : Array();
			$output = $this->inputs[$name]->outputHtml(!$isToken ? $this->data : array(), $errors);
			$this->outputtedFields[] = $name;
		}
		else {
			$output = "Form field '{$name}' is unregistered";
		}
		return $output;
	}
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
	public function resetRemainingFields() {
		$this->outputtedFields = Array();
	}
	public function getErrors() {
		return $this->errors;
	}
	public function hasErrors() {
		return (sizeof($this->errors) > 0);
	}
	public function getErrorHtmlArray() {
		$data = Array();
		foreach ($this->errors as $eKey => $eValue) {
			$data[$eKey] = $eValue->render();
		}
		return $data;
	}
	public function renderFormError($name, $type = null) {
		if (isset($this->errors[$name])) {
			return $this->errors[$name]->render($type);
		}
	}
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
}