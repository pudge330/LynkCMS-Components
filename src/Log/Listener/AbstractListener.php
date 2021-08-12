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
use LynkCMS\Component\Log\Event\LogEvent;

/**
 * Abstract logger event listener.
 */
class AbstractListener extends ContainerAwareClass {

	/**
	 * @var string Log record section delimeter/separator.
	 */
	protected $sectionDelimeter = ':';

	public function __construct() {}

	/**
	 * Map event data to placeholders from event object. Return an associative array of placeholder data pairs.
	 * 
	 * @param LogEvent The log event.
	 * 
	 * @return mixed Return value of the callback.
	 */
	protected function mapEventData(LogEvent $event) {
		$data = [];
		$data['timestamp'] = date('F j, Y h:i:s A');
		$ip = \lynk\getIp();
		if ($ip) {
			$data['ip_address'] = $ip;
		}
		$data['level'] = $event->getLevelName();
		$data['message'] = $event->getMessage();
		if (sizeof($event->getContext())) {
			$data['context'] = json_encode($event->getContext());
		}
		return $data;
	}
}