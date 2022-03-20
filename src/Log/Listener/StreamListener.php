<?php
/**
 * This file is part of the Lynk Components Package.
 *
 * (c) Brandon Garcia <me@bgarcia.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Lynk Components
 * @subpackage Logger
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Log\Listener;

use Closure;
use Lynk\Component\Log\Event\LogEvent;

/**
 * Stream listener class. Appends logs to a file.
 */
class StreamListener extends AbstractListener {

	/**
	 * @var string Log record section delimeter/separator.
	 */
	protected $sectionDelimeter = '::';

	/**
	 * @var string Log file path.
	 */
	protected $filePath;

	/**
	 * @var Closure Data mapping closure.
	 */
	protected $dataMapping;

	/**
	 * @param string $filePath Log file path.
	 */
	public function __construct($filePath, Closure $dataMapping = null) {
		parent::__construct();
		$this->filePath = $filePath;
		$this->dataMapping = $dataMapping;
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

		$data = $this->mapEventData($event);
		if ($this->dataMapping) {
			$data = ($this->dataMapping)($event, $data);
		}

		$template = '';
		foreach ($data as $key => $value) {
			if (!in_array($key, ['message', 'level', 'context'])) {
				$template .= "[%{$key}%]";
			}
		}
		$template .= '[%level%] %message%';
		if (
			isset($data['context']) &&
			((is_array($data['context']) && sizeof($data['context'])) ||
			($data['context'] && $data['context'] != ''))
		) {
			$template .= " {$this->sectionDelimeter} %context% {$this->sectionDelimeter}";
			$data['context'] = is_array($data['context']) ? json_encode($data['context']) : $data['context'];
		}

		file_put_contents(
			$this->filePath
			,\lynk\interpolate($template, $data, ['%']) . "\n"
			,FILE_APPEND
		);
	}
}