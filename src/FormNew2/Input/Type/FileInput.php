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
use Lynk\Component\FormNew2\Input\Type\View\FileView;

/**
 * Range input type.
 */
class FileInput extends InputType {

	/**
	 * @var string Input field name.
	 */
	protected $fieldName = 'fileField';

	/**
	 * Process input settings.
	 * 
	 * @param StandardContainer $settings Input settings.
	 * 
	 * @return StandardContainer Processed input settings.
	 */
	public function processSettings($settings) {
		if ($settings->options->accept) {
			$accepted = explode(',', $settings->options->accept);
			$fileIndex = DataValidator::getFileIndex();
			if (sizeof($accepted) > 0 && $accepted[0] != '*') {
				for ($i = 0; $i < sizeof($accepted); $i++) {
					if (array_key_exists($accepted[$i], $fileIndex)) {
						$settings->options->fileTypes .= implode('|', $fileIndex[$accepted[$i]]['types']);
						$settings->options->fileExts .= '.' . implode('|.', $fileIndex[$accepted[$i]]['exts']);
						if ($i < sizeof($accepted) - 1) {
							$settings->options->fileTypes .= '|';
							$settings->options->fileExts .= '|';
						}
					}
				}
			}
		}
		return $settings;
	}

	/**
	 * Create view class.
	 * 
	 * @return InputView View instance
	 */
	protected function createView() {
		return new FileView($this);
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
		if ($this->settings->options->required && (!isset($data[$this->name]) || !$data[$this->name] || (!isset($data[$this->name]['error']) || $data[$this->name]['error'] == 4)))
			return [false, "{$displayName} is required"];
		else if ($data[$this->name] && $data[$this->name]['error'] == 0) {
			$fileResult = $this->validator->file(
				$data[$this->name]['tmp_name'], 
				$this->settings->options->accept,
				$this->settings->options->maxsize,
				strtolower(pathinfo($data[$this->name]['name'], PATHINFO_EXTENSION))
			);
			if (!$fileResult && $this->settings->options->accept) {
				$type = explode(',', $this->settings->options->accept);
				$last = array_pop($type);
				$type = implode(', ', $type) . " or {$last} file";
				if ($this->settings->options->maxsize)
					$type .= " and smaller than " . $this->helper->formatFileSize($this->settings->options->maxsize);
				return [false, "{$displayName} must be a {$type}"];
			}
			else
				[true];
		}
		return [true];
	}
}