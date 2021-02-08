<?php
namespace BGStudios\Component\Util\Timer;

use Datetime;
use BGStudios\Component\Util\Timer\Event\LogEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Timer {
	protected $log;
	protected $lastLogTime;
	protected $logFile;
	protected $allowTimer;
	protected $eventDispatcher;
	public function __construct($logFile, $allowTimer = true, $eventDispatcher = null) {
		$dt = $this->currentTime();
		$this->log = Array();
		$this->logFile = $logFile;
		$this->lastLogTime = (double)$dt->format('s.u');
		$this->allowTimer = $allowTimer;
		$this->eventDispatcher = $eventDispatcher;
	}
	public function getEventDispatcher() {
		return $this->eventDispatcher;
	}
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
	public function flush() {
		$this->log[] = "Runtime                        | " . round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 5) . " seconds";
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
		$handle = fopen($this->logFile, "a+");
		fwrite($handle, $uri . PHP_EOL . implode(PHP_EOL, $this->log) . PHP_EOL . str_repeat('-', 71) . PHP_EOL);
		fclose($handle);
		$this->log = null;
		$this->lastLogTime = null;
	}
	public function currentTime() {
		$t = microtime(true);
		$micro = sprintf("%06d",($t - floor($t)) * 1000000);
		$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
		return $d;
	}
}