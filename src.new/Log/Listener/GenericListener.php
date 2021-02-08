<?php
namespace LynkCMS\Component\Log\Listener;

class GenericCallableListener extends AbstractListener {
	private $callable;
	public function __construct($callable) {
		parent::__construct();
		$this->callable = $callable;
	}
	public function handle($event) {
		return call_user_func($this->callable, $event);
	}
}