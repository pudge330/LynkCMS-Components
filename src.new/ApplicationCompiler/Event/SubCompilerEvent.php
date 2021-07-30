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
 * @subpackage ApplicationCompiler
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\ApplicationCompiler\Event;

use Symfony\Component\EventDispatcher\Event;
use LynkCMS\Component\ApplicationCompiler\SubCompiler\AbstractCompiler;

/**
 * Application compiler sub-compiler event.
 */
class SubCompilerEvent extends Event {

	/**
	 * @var string Event name.
	 */
	const NAME = 'build.sub_compilers';

	/**
	 * @var string Root directory.
	 */
	protected $root;

	/**
	 * @var string Environment type.
	 */
	protected $env;

	/**
	 * @var Array List of sub-compilers.
	 */
	protected $subCompilers;

	/**
	 * @param string $root Root directory.
	 * @param string $env Environment type.
	 */
	public function __construct($root, $env) {
		$this->root = $root;
		$this->env = $env;
		$this->subCompilers = Array();
	}

	/**
	 * Get root directory.
	 * 
	 * @return string Root directory.
	 */
	public function getRoot() {
		return $this->root;
	}

	/**
	 * Get environment.
	 * 
	 * @return string Environment.
	 */
	public function getEnv() {
		return $this->env;
	}

	/**
	 * Get sub-compilers.
	 * 
	 * @return Array Sub-compilers.
	 */
	public function getSubCompilers() {
		return $this->subCompilers;
	}

	/**
	 * Set sub-compiler.
	 * 
	 * @param AbstractCompiler Sub-compiler instance.
	 */
	public function setSubCompiler(AbstractCompiler $compiler) {
		$this->subCompilers = $compiler;
	}

	/**
	 * Merge sub-compilers.
	 * 
	 * @param Array Array of sub-compiler instances.
	 */
	public function mergeSubCompilers($compilers) {
		foreach ($compilers as $compiler) {
			$this->setSubCompiler($compiler);
		}
	}

	/**
	 * Check if sub-compilers exists.
	 * 
	 * @return bool True if sub-compilers exists, false otherwise.
	 */
	public function hasSubCompilers() {
		return (sizeof($this->subCompilers) > 0);
	}
}