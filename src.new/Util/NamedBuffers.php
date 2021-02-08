<?php
/**
 * This file is part of the LynkCMS Util Component.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS PHP Components
 * @subpackage Command
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Util;

/**
 * Names output buffers for rendering content and using a output buffer to cache it for later retrieval.
 */
class NamedBuffers {

	/**
	 * @var Array Array of rendered content.
	 */
	private $buffers;

	/**
	 * @var string Name of the currently opened buffer.
	 */
	private $current;

	/**
	 * @var Array Current stack of buffers by name.
	 */
	private $currentStack;

	public function __construct() {
		$this->buffers = array();
		$this->current = null;
		$this->currentStack = array();
	}

	/**
	 * Atart a new output buffer.
	 *
	 * @param string $name The name of the buffer content.
	 */
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

	/**
	 * Stop and close currently opened buffer.
	 */
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

	/**
	 * Stop and close all buffers, current and in the stack.
	 */
	public function stopAll() {
		while ($this->current) {
			$this->stop();
		}
	}

	/**
	 * Get contents from buffer.
	 *
	 * @param string $name The name of the buffered content.
	 * @param bool $clean Optional. Whether or not to clean the buffer content.
	 * @return string The buffered content or null if not in buffer stack.
	 */
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

	/**
	 * Manually set named buffer.
	 *
	 * @param string $name The name of the buffered content.
	 * @param string $buffer The content.
	 */
	public function set($name, $buffer) {
		$this->buffers[$name] = $buffer;
	}

	/**
	 * Check whether or not a named buffer exists.
	 *
	 * @param string $name The name of the buffer.
	 * @return bool True if exists or false if not.
	 */
	public function has($name) {
		if (isset($this->buffers[$name]))
			return true;
		else
			return false;
	}

	/**
	 * Get all bufferes as a associative array.
	 *
	 * @param bool $clean Optional. Whether or not to clean the buffer content.
	 * @return Array Asssociative array of buffered content.
	 */
	public function getAll($clean = true) {
		if ($clean) {
			$buffers = $this->buffers;
			$this->cleanAll();
			return $buffers;
		}
		else
			return $this->buffers;
	}

	/**
	 * Get array of all names of buffers.
	 *
	 * @return Array Array of buffer names.
	 */
	public function getNames() {
		return array_keys($this->buffers);
	}

	/**
	 * Clean a buffer.
	 *
	 * @param string Name of the buffer.
	 */
	public function clean($name) {
		if ($this->current == $name)
			$this->stop();
		unset($this->buffers[$name]);
	}

	/**
	 * Clean all buffers.
	 */
	public function cleanAll() {
		$this->stopAll();
		$this->buffers = [];
	}
}