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

use Closure;

/**
 * Option trait, shared class option functionality.
 */
trait OptionTrait {

	/**
	 * @var Array Key and value options.
	 */
	private $__options = array();

	/**
	 * Set option.
	 * 
	 * @param string $key Option key.
	 * @param mixed $option Optional. Option value.
	 */
	public function setOption($key, $option = null) {
		if (is_array($key)) {
			if ($option) {
				$this->__options = $key;
			}
			else {
				foreach ($key as $k => $v) {
					$this->__options[$k] = $v;
				}
			}
		}
		else {
			$this->__options[$key] = $option;
		}
	}

	/**
	 * Get option.
	 * 
	 * @param string $key Option key.
	 * 
	 * @return mixed Option value.
	 */
	public function getOption($key) {
		if ($this->hasOption($key))
			return $this->__options[$key];
	}

	/**
	 * Check if option exists.
	 * 
	 * @param string $key Option key.
	 * 
	 * @return bool True if option exists, false if not.
	 */
	public function hasOption($key) {
		return array_key_exists($key, $this->__options);
	}

	/**
	 * Remove option.
	 * 
	 * @param string $key Option key.
	 */
	public function removeOption($key) {
		unset($this->__options[$key]);
	}

	/**
	 * Get all options.
	 * 
	 * @return Array List of options.
	 */
	public function getAllOptions() {
		return $this->__options;
	}

	/**
	 * Get all option keys.
	 * 
	 * @return Array List of option keys.
	 */
	public function getOptionKeys() {
		return array_keys($this->__options);
	}

	/**
	 * Loop through all options and call closure. Passing in index, key and value as parameters.
	 * 
	 * @param Closure Callable function closure.
	 */
	public function eachOption(Closure $callback) {
		$index = -1;
		foreach ($this->__options as $okey => &$oval) {
			$index++;
			$callback($index, $okey, $oval);
		}
	}
}