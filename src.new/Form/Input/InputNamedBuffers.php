<?php
namespace LynkCMS\Component\Form\Input;

class InputNamedBuffers {
	private $buffers;
	private $current;
	private $currentStack;
	public function __construct() {
		$this->buffers = array();
		$this->current = null;
		$this->currentStack = array();
	}
	public function start($name) {
		if ($this->current) {
			if (!$this->has($this->current))
				$this->buffers[$this->current] = '';
			$this->buffers[$this->current] .= ob_get_contents();
			ob_end_clean();
			$this->currentStack[] = $this->current;
		}
		$this->current = $name;
		ob_start();
	}
	public function stop() {
		if ($this->current) {
			if (!$this->has($this->current))
				$this->buffers[$this->current] = '';
			$this->buffers[$this->current] .= ob_get_contents();
			ob_end_clean();
			$this->current = null;
			if (sizeof($this->currentStack) > 0) {
				$poppedCurrent = array_pop($this->currentStack);
				$this->start($poppedCurrent);
			}
		}
	}
	public function stopAll() {
		while ($this->current) {
			$this->stop();
		}
	}
	public function get($name, $clean = true) {
		if ($this->current == $name)
			$this->stop();
		if ($this->has($name)) {
			$buffer = $this->buffers[$name];
			if ($clean)
				$this->clean($name);
			return $buffer;
		}
	}
	public function set($name, $buffer) {
		$this->buffers[$name] = $buffer;
	}
	public function has($name) {
		if (isset($this->buffers[$name]))
			return true;
		else
			return false;
	}
	public function getAll($clean = true) {
		if ($clean) {
			$buffers = $this->buffers;
			$this->cleanAll();
			return $buffers;
		}
		else
			return $this->buffers;
	}
	public function getNames() {
		return array_keys($this->buffers);
	}
	public function clean($name) {
		if ($this->current == $name)
			$this->stop();
		unset($this->buffers[$name]);
	}
	public function cleanAll() {
		$this->stopAll();
		$this->buffers = [];
	}
}