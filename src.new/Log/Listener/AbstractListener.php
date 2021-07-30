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

use LynkCMS\Component\Container\ContainerAwareClass;

/**
 * Abstract logger event listener.
 */
class AbstractListener extends ContainerAwareClass {

	/**
	 * @var string Log record section delimeter/separator.
	 */
	protected $sectionDelimeter = ':';

	/**
	 * Interpolate log, level and context into log entry.
	 * 
	 * @param string $level Log level.
	 * @param string $message Log message.
	 * @param Array $context Message context.
	 * 
	 * @return string Log record.
	 */
	public function interpolateLog($level, $message, array $context = array()) {
		$userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : 0;
		$record = "[%datetime%][%ip%][%uid%][%level%] %message%";
		$recordContext = array(
			'datetime' => date($this->datetimeFormat)
			,'level' => $level
			,'message' => $message
			,'ip' => \bgs\getIp()
			,'uid' => $userId
		);
		if (sizeof($context) > 0) {
			$record .= "{$this->sectionDelimeter}%context%\n";
			$recordContext['context'] = json_encode($context);
		}
		else
			$record .= "\n";
		return \bgs\interpolate($record, $recordContext, array('%'));
	}
}