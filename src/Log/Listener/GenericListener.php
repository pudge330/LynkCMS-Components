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

namespace LynkCMS\Component\Log\Listener;

use LynkCMS\Component\Log\Event\LogEvent;

/**
 * Generic listener class. Calls a closure.
 */
class GenericListener extends AbstractListener {

	/**
	 * @var mixed Callable function or closure.
	 */
	protected $callable;

	/**
	 * @param mixed $callable Callable function or closure.
	 */
	public function __construct($callable) {
		parent::__construct();
		$this->callable = $callable;
	}

	/**
	 * Handle the event.
	 * 
	 * @param LogEvent The log event.
	 * 
	 * @return mixed Return value of the callback.
	 */
	public function handle(LogEvent $event) {
		return call_user_func($this->callable, $event);
	}
}