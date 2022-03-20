<?php
/**
 * This file is part of the Lynk Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Lynk Components
 * @subpackage Logger
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Log;

use Lynk\Component\Log\Event\LogEvent;
use Lynk\Component\Log\Listener\AbstractListener;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * PSR compatible logging class.
 */
class Logger implements LoggerInterface {

	/**
	 * @var string Custom log key.
	 */
	const CUSTOM = 'custom';

	/**
	 * @var string Debug log key.
	 */
	const DEBUG = LogLevel::DEBUG;

	/**
	 * @var string Info log key.
	 */
	const INFO = LogLevel::INFO;

	/**
	 * @var string Notice log key.
	 */
	const NOTICE = LogLevel::NOTICE;

	/**
	 * @var string Warning log key.
	 */
	const WARNING = LogLevel::WARNING;

	/**
	 * @var string Error log key.
	 */
	const ERROR = LogLevel::ERROR;

	/**
	 * @var string Critical log key.
	 */
	const CRITICAL = LogLevel::CRITICAL;

	/**
	 * @var string Alert log key.
	 */
	const ALERT = LogLevel::ALERT;

	/**
	 * @var string Emergency log key.
	 */
	const EMERGENCY = LogLevel::EMERGENCY;

	/**
	 * @var Symfony\Component\EventDispatcher\EventDispatcher Symfony event dispatcher.
	 */
	private $eventDispatcher;

	/**
	 * @var string Logger instance name.
	 */
	private $loggerName;

	/**
	 * @param string $name Logger instance name.
	 * @param array $handler Array of event listeners or event listeners and levels in sub-array.
	 */
	public function __construct($name, array $listeners = array()) {
		$this->loggerName = $name;
		$this->eventDispatcher = new EventDispatcher();
		foreach ($listeners as $listener) {
			if (is_array($listener)) {
				$this->registerListener($listener[0], $listener[1]);
			}
			else {
				$this->registerListener($listener);
			}
		}
	}

	/**
	 * Get event dispatcher.
	 * 
	 * @return Symfony\Component\EventDispatcher\EventDispatcher The event dispatcher.
	 */
	public function getEventDispatcher() {
		return $this->eventDispatcher;
	}

	/**
	 * Register event listener.
	 * 
	 * @param mixed $listener Event listeners, either closure or class.
	 * @param string $level Optional. Listen on a specific event level.
	 */
	public function registerListener($listener, $level = null) {
		$level = $level ? ".{$level}" : '';
		if ($listener instanceof AbstractListener) {
			$this->eventDispatcher->addListener("log{$level}", [$listener, 'handle']);
		}
		else {
			$this->eventDispatcher->addListener("log{$level}", $listener);
		}
	}

	/**
	 * Get logger instance name.
	 */
	public function getName() {
		return $this->loggerName;
	}

	/**
	 * Generic internal logging method.
	 * 
	 * @param string $level Log level.
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 * @param string $clevelName Optional. Custom log level name.
	 */
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
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function emergency($message, array $context = array()) {
		$this->recordLog(self::EMERGENCY, $message, $context);
	}

	/**
	 * Action must be taken immediately.
	 *
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function alert($message, array $context = array()) {
		$this->recordLog(self::ALERT, $message, $context);
	}

	/**
	 * Critical conditions.
	 *
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function critical($message, array $context = array()) {
		$this->recordLog(self::CRITICAL, $message, $context);
	}

	/**
	 * Runtime errors.
	 *
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function error($message, array $context = array()) {
		$this->recordLog(self::ERROR, $message, $context);
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function warning($message, array $context = array()) {
		$this->recordLog(self::WARNING, $message, $context);
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function notice($message, array $context = array()) {
		$this->recordLog(self::NOTICE, $message, $context);
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function info($message, array $context = array()) {
		$this->recordLog(self::INFO, $message, $context);
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function debug($message, array $context = array()) {
		$this->recordLog(self::DEBUG, $message, $context);
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level Log level.
	 * @param string $message Log message.
	 * @param Array $context Optional. Message context.
	 */
	public function log($level, $message, array $context = array()) {
		if (in_array($level, array(self::EMERGENCY, self::ALERT, self::CRITICAL, self::ERROR, self::WARNING, self::NOTICE, self::INFO, self::DEBUG)))
			$this->{$level}($message, $context);
		else
			$this->recordLog(self::CUSTOM, $message, $context, $level);
	}
}