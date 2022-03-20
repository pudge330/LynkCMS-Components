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
 * @subpackage SiteCrawler
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\SiteCrawler\Event;

/**
 * Link event.
 */
class LinkEvent extends AbstractException {

	/**
	 * @var string Event name.
	 */
	const NAME = 'site-crawler.link';

	/**
	 * @var string URL the event was triggered on.
	 */
	protected $link;

	/**
	 * @var bool Whether or not to prevent the HTTP request.
	 */
	protected $preventRequest;

	/**
	 * @param string $link URL the event was triggered on.
	 */
	public function __construct($link) {
		$this->link = $link;
		$this->preventRequest = false;
	}

	/**
	 * Get the link.
	 * 
	 * @return string The link.
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 * Set the link.
	 * 
	 * @param string $link The modified link.
	 */
	public function setLink($link) {
		$this->link = $link;
	}

	/**
	 * Prevent the HTTP request.
	 */
	public function preventRequest() {
		$this->preventRequest = true;
	}

	/**
	 * Check if the request has been prevented.
	 * 
	 * @return bool Whether or not the request has been prevented.
	 */
	public function isRequestPrevented() {
		return $this->preventRequest;
	}
}