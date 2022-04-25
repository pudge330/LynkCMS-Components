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

namespace Lynk\Component\FormNew2\Input\Type;

use Lynk\Component\FormNew2\Input\InputType;
use Lynk\Component\FormNew2\Input\Type\View\TextareaView;

/**
 * Textarea input type.
 */
class TextareaInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'textareaField';

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new TextareaView($this);
	}

	/**
	 * Validate submitted data value.
	 * 
	 * @param Array $data Data values.
	 * 
	 * @return Array Boolean as first value that indicates whether or not the value was valid.
	 *               Second ootional value describes the error.
	 */
	public function validateData($data) {
		$displayName = $this->settings->label ? $this->settings->label : $this->settings->errorName;
		if ($this->settings->options->required && (!isset($data[$this->name]) || ($data[$this->name] === null || $data[$this->name] == '')))
			return [false, "{$displayName} is required"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->min && $this->settings->options->max && !$this->validator->text($data[$this->name], $this->settings->options->max, $this->settings->options->min))
			return [false, "{$displayName} must be between {$this->settings->options->min} and {$this->settings->options->max} characters"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->max && !$this->validator->text($data[$this->name], $this->settings->options->max))
			return [false, "{$displayName} must be {$this->settings->options->max} characters or less"];
		else if (isset($data[$this->name]) && $data[$this->name] && $this->settings->options->min && strlen($data[$this->name]) < $this->settings->options->min)
			return [false, "{$displayName} must be {$this->settings->options->min} characters or more"];
		else
			return [true];
	}
}