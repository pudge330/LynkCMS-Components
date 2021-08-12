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
 * Stream listener class. Appends logs to a file.
 */
class StreamListener extends AbstractListener {

	/**
	 * @var string Log file path.
	 */
	protected $filePath;

	/**
	 * @param string $filePath Log file path.
	 */
	public function __construct($filePath) {
		parent::__construct();
		$this->filePath = $filePath;
	}

	/**
	 * Handle the event. Write log to file.
	 * 
	 * @param LogEvent The log event.
	 * 
	 * @return mixed Return value of the callback.
	 */
	public function handle(LogEvent $event) {
		if (!file_exists($this->filePath))
			file_put_contents($this->filePath, '');
		$level = $event->getLevelName();
		file_put_contents(
			$this->filePath
			,$this->interpolateLog($level, $event->getMessage(), $event->getContext())
			,FILE_APPEND
		);
	}
}