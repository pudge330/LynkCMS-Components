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

use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract SiteCrawlere exception class.
 */
class AbstractException extends Event {

	/**
	 * @var bool Whether or not the site crawler should be stopped.
	 */
	protected $siteCrawlStopped = false;

	/**
	 * Stop the site crawler.
	 */
	public function stopSiteCrawler() {
		$this->siteCrawlStopped = true;
	}

	/**
	 * Check if site crawler has been stopped.
	 * 
	 * @return bool Whether or not the site crawler is stopped.
	 */
	public function isSiteCrawlerStopped() {
		return $this->siteCrawlStopped;
	}
}