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

namespace LynkCMS\Component\Util\Timer\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Timer log event.
 */
class LogEvent extends Event {

	/**
	 * @var string Event name/identifier.
	 */
	const NAME = 'util.timer.log';

	/**
	 * @var string Log message.
	 */
	protected $message;

	/**
	 * @var object|Array Log message context.
	 */
	protected $context;

	/**
	 * @var DateTime Log message datetime object.
	 */
	protected $datetime;

	/**
	 * @var float Time difference since last log in seconds and milliseconds.
	 */
	protected $diffTime;

	/**
	 * @param string $message Log message.
	 * @param object|Array|null $context Log message context.
	 * @param DateTime Log message datetime object.
	 * @param float $diffTime Time difference since last log in seconds and milliseconds.
	 */
	public function __construct($message, $context, $datetime, $diffTime) {
		$this->message = $message;
		$this->context = $context;
		$this->datetime = $datetime;
		$this->diffTime = $diffTime;
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
	 * Get log message context.
	 *
	 * @return object|Array|null Log message context.
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * Get log message datetime object.
	 *
	 * @param DateTime Log message datetime object.
	 */
	public function getDatetime() {
		return $this->datetime;
	}

	/**
	 * Get time difference since last log.
	 *
	 * @return float Time difference since last log in seconds and milliseconds.
	 */
	public function getDiffTime() {
		return $this->diffTime;
	}
}