<?php
namespace LynkCMS\Component\Log\Handler;

class StreamListener extends AbstractListener {
	private $filePath;
	public function __construct($filePath) {
		parent::__construct();
		$this->filePath = $filePath;
	}
	public function handle($event) {
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