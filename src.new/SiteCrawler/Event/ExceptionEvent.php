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
 * @subpackage SiteCrawler
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\SiteCrawler\Event;

use Exception;

/**
 * Exception event.
 */
class ExceptionEvent extends AbstractException {

	/**
	 * @var string Event name.
	 */
	const NAME = 'site-crawler.exception';

	/**
	 * @var string URL the event was triggered on.
	 */
	protected $link;

	/**
	 * @var Exeption The exception object.
	 */
	protected $exception;

	/**
	 * @param string $link URL the event was triggered on.
	 * @param Exception $exception The exception object.
	 */
	public function __construct($link, Exception $exception) {
		$this->link = $link;
		$this->exception = $exception;
	}

	/**
	 * Get the link.
	 * 
	 * @return sstring The link.
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 * Get the exception.
	 * 
	 * @return Exception The exception.
	 */
	public function getException() {
		return $this->exception;
	}
}