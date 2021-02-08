<?php
namespace LynkCMS\Component\ApplicationCompiler\Event;

use Symfony\Component\EventDispatcher\Event;

class InjectionEvent extends Event {
	const NAME = 'build.injection';
	protected $root;
	protected $env;
	protected $injections;
	public function __construct($root, $env , $config, $injections = Array()) {
		$this->root = $root;
		$this->env = $env;
		$this->config = $config;
		$this->injections = $injections;
		foreach ($this->injections as $key => $val) {
			if (!is_array($val)) {
				$this->injections[$key] = Array();
				$this->injections[$key]['placeholder'] = $val;
				$this->injections[$key]['content'] = '';
			}
			else if (!isset($val['content'])) {
				$this->injections[$key]['content'] = '';
			}
		}
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
	public function addInjection($key, $placeholder, $content = '') {
		$this->injections[$key] = Array('placeholder' => $placeholder, 'content' => $content);
	}
	public function getInjections() {
		return $this->injections;
	}
	public function getOutput($key) {
		if ($this->hasInjection($key))
			return $this->injections[$key]['content'];
	}
	public function setOutput($key, $injection) {
		$this->injections[$key]['content'] = $injection;
	}
	public function appendOutput($key, $injection) {
		if ($this->hasInjection($key))
		$this->injections[$key]['content'] .= $injection;
	}
	public function hasInjection($key) {
		return array_key_exists($key, $this->injections);
	}
}