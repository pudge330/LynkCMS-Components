<?php
namespace LynkCMS\Component\ApplicationCompiler\Event;

use Symfony\Component\EventDispatcher\Event;
use LynkCMS\Component\ApplicationCompiler\SubCompiler\AbstractCompiler;

class SubCompilerEvent extends Event {
	const NAME = 'build.sub_compilers';
	protected $root;
	protected $env;
	protected $subCompilers;
	public function __construct($root, $env) {
		$this->root = $root;
		$this->env = $env;
		$this->subCompilers = Array();
	}
	public function getRoot() {
		return $this->root;
	}
	public function getEnv() {
		return $this->env;
	}
	public function getSubCompilers() {
		return $this->subCompilers;
	}
	public function setSubCompiler(AbstractCompiler $compiler) {
		$this->subCompilers = $compiler;
	}
	public function mergeSubCompilers($compilers) {
		foreach ($compilers as $compiler) {
			$this->setSubCompiler($compiler);
		}
	}
	public function hasSubCompilers() {
		return (sizeof($this->subCompilers) > 0);
	}
}