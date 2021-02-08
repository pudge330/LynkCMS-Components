<?php
namespace BGStudios\Component\Util\Timer\Event;

use Symfony\Component\EventDispatcher\Event;

class LogEvent extends Event {
	const NAME = 'util.timer.log';
	protected $message;
	protected $context;
	protected $datetime;
	protected $diffTime;
	public function __construct($message, $context, $datetime, $diffTime) {
		$this->message = $message;
		$this->context = $context;
		$this->datetime = $datetime;
		$this->diffTime = $diffTime;
	}
	public function getMessage() {
		return $this->message;
	}
	public function getContext() {
		return $this->context;
	}
	public function getDatetime() {
		return $this->datetime;
	}
	public function getDiffTime() {
		return $this->diffTime;
	}
}