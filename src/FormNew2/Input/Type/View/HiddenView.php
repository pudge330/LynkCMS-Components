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

namespace Lynk\Component\FormNew2\Input\Type\View;

use Lynk\Component\FormNew2\Input\InputView;

/**
 * Hidden view class.
 */
class HiddenView extends InputView {

	/**
	 * Applies form field classes to attribute array.
	 * 
	 * @param array &$attr Attributes array by reference.
	 * @param array $classes Classes to apply.
	 */
	public function applyFormFieldClasses(array &$attr, array $classes = null) {}

	/**
	 * Render input label.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered label.
	 */
	public function renderLabel(&$attr, $values = [], &$errors = []) {}

	/**
	 * Render input.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered input.
	 */
	public function renderInput(&$attr, $values = [], &$errors = []) {
		$classes = $this->getFieldClasses();
		$this->input->getSettings()->options->inputType = 'hidden';
		$this->input->getHelper()->addAttrClass($attr, 'input', $classes['wrap']);
		$this->input->getHelper()->addAttrClass($attr, 'input', $this->input->getFieldName());
		$this->input->getHelper()->addAttrClass($attr, 'input', $classes['input']);
		return parent::renderInput($attr, $values, $errors) . "\n";
	}

	/**
	 * Render input help.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered help.
	 */
	public function renderHelp(&$attr, $values = [], &$errors = []) {}

	/**
	 * Render input error.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered error.
	 */
	public function renderError(&$attr, $values = [], &$errors = []) {}

	/**
	 * Render JSON data.
	 * 
	 * @param Array $attr Input attributes.
	 * @param Array $values Optional. Submitted form values.
	 * @param Array $errors Optional. Form errors.
	 * 
	 * @return string Rendered JSON data.
	 */
	public function renderJSONData(&$attr, $values = [], &$errors = []) {}

	/**
	 * Render field wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderFieldWrap($part, $settings, $attr) {
		return $part;
	}

	/**
	 * Render label wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderLabelWrap($part, $settings, $attr) {}

	/**
	 * Render input wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderInputWrap($part, $settings, $attr) {
		return $part;
	}

	/**
	 * Render help wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderHelpWrap($part, $settings, $attr) {}

	/**
	 * Render error wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderErrorWrap($part, $settings, $attr) {}

	/**
	 * Render data wrap.
	 * 
	 * @param string $part Input part.
	 * @param StandardContainer $settings Input settings.
	 * @param Array $attr Input attributes.
	 * 
	 * @return string Rendered HTML code.
	 */
	public function renderDataWrap($part, $settings, $attr) {}
}