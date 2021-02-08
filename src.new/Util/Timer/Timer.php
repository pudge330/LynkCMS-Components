<?php
/**
 * This file is part of the LynkCMS Util Component.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS PHP Components
 * @subpackage Command
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Util\Timer;

use Datetime;
use LynkCMS\Component\Util\Timer\Event\LogEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Timer class for timing code execution or processes.
 */
class Timer {

	/**
	 * @var Array Log messages.
	 */
	protected $log;

	/**
	 * @var string Last log time in seconds and milliseconds.
	 */
	protected $lastLogTime;

	/**
	 * @var string Path to log file.
	 */
	protected $logFile;

	/**
	 *
	 */
	protected $eventDispatcher;

	/**
	 * @param string $logFile Path to the log file.
	 * @param Symfony\Component\EventDispatcher\EventDispatcher Optional. Instance of symfony event dispatcher.
	 */
	public function __construct($logFile, $eventDispatcher = null) {
		$dt = $this->currentTime();
		$this->log = Array();
		$this->logFile = $logFile;
		$this->lastLogTime = (double)$dt->format('s.u');
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * Get the instance of event dispatcher.
	 *
	 * @return Symfony\Component\EventDispatcher\EventDispatcher|null The instance of Symfony EventDispatcher or null if not set.
	 */
	public function getEventDispatcher() {
		return $this->eventDispatcher;
	}

	/**
	 * Log a message.
	 *
	 * @param string $message The message to log.
	 * @param object|Array Optional. Object or array to be logged as a JSON string for context to message.
	 */
	public function log($message, $context = null) {
		$dt = $this->currentTime();
		if ($context)
			$context = is_object($context) || is_array($context) ? ' | ' . json_encode($context) : $context;
		$nowtime = (double)$dt->format('s.u');
		$difftime = $nowtime - $this->lastLogTime;
		if ($this->eventDispatcher) {
			$event = new LogEvent($message, $context, $dt, $difftime);
			$this->eventDispatcher->dispatch(LogEvent::NAME, $event);
		}
		$this->log[] = sprintf("%-30s | %-30s | %-3f", $message, $dt->format('F Y h:i:s.u A'), $difftime) . $context;
		$this->lastLogTime = $nowtime;
	}

	/**
	 * Flush and print the log to the log file.
	 */
	public function flush() {
		$this->log[] = "Runtime                        | " . round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 5) . " seconds";
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
		$handle = fopen($this->logFile, "a+");
		fwrite($handle, $uri . PHP_EOL . implode(PHP_EOL, $this->log) . PHP_EOL . str_repeat('-', 71) . PHP_EOL);
		fclose($handle);
		$this->log = null;
		$this->lastLogTime = null;
	}

	/**
	 * Get formatted current timestamp.
	 *
	 * @return DateTime Current datetime as object.
	 */
	public function currentTime() {
		$t = microtime(true);
		$micro = sprintf("%06d",($t - floor($t)) * 1000000);
		$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
		return $d;
	}
}