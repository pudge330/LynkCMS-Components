<?php
namespace LynkCMS\Component\ApplicationCompiler\Event;

use Symfony\Component\EventDispatcher\Event;

class ConfigEvent extends Event {
	const NAME = 'build.config';
	protected $root;
	protected $env;
	protected $config;
	public function __construct($root, $env, $config) {
		$this->root = $root;
		$this->env = $env;
		$this->config = $config;
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
	public function setConfig($config) {
		return $this->config = $config;
	}
	public function mergeConfig($config) {
		return $this->config = \bgs\deepMerge($this->config, $config);
	}
}