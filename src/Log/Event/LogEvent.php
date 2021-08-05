<?php
/**
 * This file is part of the LynkCMS Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package LynkCMS Components
 * @subpackage Logger
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Log\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Logger log event.
 */
class LogEvent extends Event {

	/**
	 * @var string Event name.
	 */
	protected $name;

	/**
	 * @var string Log level.
	 */
	protected $level;

	/**
	 * @var string Log message.
	 */
	protected $message;

	/**
	 * @var Array Message context.
	 */
	protected $context;

	/**
	 * @var string Custom log level name.
	 */
	protected $levelName;

	/**
	 * @param string $name Event name.
	 * @param string $level Log level.
	 * @param string $message Log message.
	 * @param Array $context Message context.
	 * @param string $levelName Optional. Custom log level name.
	 */
	public function __construct($name, $level, $message, Array $context, $levelName = null) {
		$this->name = $name;
		$this->level = $level;
		$this->message = $message;
		$this->context = $context;
		$this->levelName = $this->level == 'custom' && $levelName ? $levelName : $this->level;
	}

	/**
	 * Get event name.
	 * 
	 * @return string Event name.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get log level.
	 * 
	 * @return string Log level.
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * Get log message.
	 * 
	 * @return string Log message.
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Set log message.
	 * 
	 * @param string $message Log message.
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Get message context.
	 * 
	 * @return Array Message context.
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * Set message context.
	 * 
	 * @param Array $context Message context.
	 */
	public function setContext(Array $context) {
		$this->context = $context;
	}

	/**
	 * Get custom log level name.
	 * 
	 * @return string Custom level name.
	 */
	public function getLevelName() {
		return $this->levelName;
	}

	/**
	 * Set custom level name.
	 * 
	 * @param string $name Custom level name.
	 */
	public function setLevelName($name) {
		if ($this->level == 'custom')
			$this->levelName = $name;
	}
}