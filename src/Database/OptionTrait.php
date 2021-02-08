<?php
namespace BGStudios\Component\Database;

trait OptionTrait {
	private $__options = array();
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
	public function getOption($key) {
		if ($this->hasOption($key))
			return $this->__options[$key];
	}
	public function hasOption($key) {
		return array_key_exists($key, $this->__options);
	}
	public function removeOption($key) {
		unset($this->__options[$key]);
	}
	public function getAllOptions() {
		return $this->__options;
	}
	public function getOptionKeys() {
		return array_keys($this->__options);
	}
	public function eachOption($callback) {
		$index = -1;
		foreach ($this->__options as $okey => &$oval) {
			$index++;
			$callback($index, $okey, $oval);
		}
	}
}