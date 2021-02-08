<?php
namespace LynkCMS\Component\ApplicationCompiler\Event;

use Symfony\Component\EventDispatcher\Event;

class CompileEvent extends Event {
	const NAME = 'build.compile';
	protected $root;
	protected $env;
	protected $config;
	protected $vars;
	public function __construct($root, $env, $config, $vars) {
		$this->root = $root;
		$this->env = $env;
		$this->config = $config;
		$this->vars = $vars;
	}
	public function getRoot() {
		return $this->root;
	}
	public function getEnv() {
		return $this->env;
	}
	public function getConfig() {
		return $this->config;
	}
	public function getVars() {
		return $this->vars;
	}
	public function getVar($key) {
		if (array_key_exists($key, $this->vars))
			return $this->vars[$key];
	}
	public function setVars($vars) {
		$this->vars = array_merge($this->vars, $vars);
	}
	public function setVar($key, $value) {
		$this->vars[$key] = $value;
	}
	public function appendVar($var, $append) {
		if (array_key_exists($var, $this->vars)) {
			$this->vars[$var] .= $append;
		}
	}
}