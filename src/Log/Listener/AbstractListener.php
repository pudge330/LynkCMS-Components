<?php
namespace BGStudios\Component\Log\Listener;

use BGStudios\Component\Container\ContainerAwareClass;

class AbstractListener extends ContainerAwareClass {
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