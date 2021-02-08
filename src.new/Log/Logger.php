<?php
namespace LynkCMS\Component\Log;

use LynkCMS\Component\Log\Event\LogEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Logger implements LoggerInterface {
	const CUSTOM = 'custom';
	const DEBUG = LogLevel::DEBUG;
	const INFO = LogLevel::INFO;
	const NOTICE = LogLevel::NOTICE;
	const WARNING = LogLevel::WARNING;
	const ERROR = LogLevel::ERROR;
	const CRITICAL = LogLevel::CRITICAL;
	const ALERT = LogLevel::ALERT;
	const EMERGENCY = LogLevel::EMERGENCY;
	private $eventDispatcher;
	private $loggerName;
	public function __construct($name, array $handlers = array()) {
		$this->loggerName = $name;
		$this->eventDispatcher = new EventDispatcher();
	}
	public function getEventDispatcher() {
		return $this->eventDispatcher;
	}
	public function registerListener($listener, $level = null) {
		$level = $level ? ".{$level}" : '';
		$this->eventDispatcher->addListener("log{$level}", $listener);
	}
	public function getName() {
		return $this->loggerName;
	}
	private function recordLog($level, $message, array $context = array(), $clevelName = null) {
		$event = new LogEvent('log', $level, $message, $context, $clevelName);
		$this->eventDispatcher->dispatch($event->getName(), $event);
		$event = new LogEvent("log.{$level}", $level, $message, $context, $clevelName);
		$this->eventDispatcher->dispatch($event->getName(), $event);
		if ($level == self::CUSTOM && $clevelName) {
			$event = new LogEvent("log.{$level}.{$clevelName}", $level, $message, $context, $clevelName);
			$this->eventDispatcher->dispatch($event->getName(), $event);
		}
	}
	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function emergency($message, array $context = array()) {
		$this->recordLog(self::EMERGENCY, $message, $context);
	}
	/**
	 * Action must be taken immediately.
	 *
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function alert($message, array $context = array()) {
		$this->recordLog(self::ALERT, $message, $context);
	}
	/**
	 * Critical conditions.
	 *
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function critical($message, array $context = array()) {
		$this->recordLog(self::CRITICAL, $message, $context);
	}
	/**
	 * Runtime errors.
	 *
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function error($message, array $context = array()) {
		$this->recordLog(self::ERROR, $message, $context);
	}
	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function warning($message, array $context = array()) {
		$this->recordLog(self::WARNING, $message, $context);
	}
	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function notice($message, array $context = array()) {
		$this->recordLog(self::NOTICE, $message, $context);
	}
	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function info($message, array $context = array()) {
		$this->recordLog(self::INFO, $message, $context);
	}
	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function debug($message, array $context = array()) {
		$this->recordLog(self::DEBUG, $message, $context);
	}
	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function log($level, $message, array $context = array()) {
		if (in_array($level, array(self::EMERGENCY, self::ALERT, self::CRITICAL, self::ERROR, self::WARNING, self::NOTICE, self::INFO, self::DEBUG)))
			$this->{$level}($message, $context);
		else
			$this->recordLog(self::CUSTOM, $message, $context, $level);
	}
}