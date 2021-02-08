<?php
namespace BGStudios\Component\Form;

class FormError {
	protected $error;
	protected $key;
	public function __construct() {
		$args = func_get_args();
		switch (sizeof($args)) {
			case 1:
				$this->key = substr(md5($args[0]), 0, 16);
				$this->error = $args[0];
			break;
			case 2:
				$this->key = preg_replace('/([^a-zA-Z0-9-_])/', '_', $args[0]);
				$this->error = $args[1];
			break;
			default:
				throw new Exception('Invalid number of arguments.');
			break;
		}
	}
	public function getError() {
		return $this->error;
	}
	public function getKey() {
		return $this->key;
	}
	public function render($type = null) {
		$output = '';
		switch ($type) {
			case 'list':
			case 'ul-list':
			case 'ol-list':
				$output = "<li class=\"formError formError_{$this->key}\">".htmlentities($this->error)."</li>\n";
			break;
			default:
				$output = "<{$type} class=\"formError formError_{$this->key}\">".htmlentities($this->error)."</{$type}>\n";
			break;
		}
		return $output;
	}
}