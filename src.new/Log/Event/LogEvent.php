<?php
namespace LynkCMS\Component\Log\Event;

use Symfony\Component\EventDispatcher\Event;

class LogEvent extends Event {
	protected $name;
	protected $level;
	protected $message;
	protected $context;
	protected $levelName;
	public function __construct($name, $level, $message, $context, $levelName = null) {
		$this->name = $name;
		$this->level = $level;
		$this->message = $message;
		$this->context = $context;
		$this->levelName = $this->level == 'custom' && $levelName ? $levelName : $this->level;
	}
	public function getName() {
		return $this->name;
	}
	public function getLevel() {
		return $this->level;
	}
	public function getMessage() {
		return $this->message;
	}
	public function setMessage($message) {
		$this->message = $message;
	}
	public function getContext() {
		return $this->context;
	}
	public function setContext($context) {
		$this->context = $context;
	}
	public function getLevelName() {
		return $this->levelName;
	}
	public function setLevelName($name) {
		if ($this->level == 'custom')
			$this->levelName = $name;
	}
}