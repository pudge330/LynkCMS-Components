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

/**
 * Unparsable link event.
 */
class UnparsableLinkEvent extends AbstractException {

	/**
	 * @var string Event name.
	 */
	const NAME = 'site-crawler.unparsable-link';

	/**
	 * @var string URL the event was triggered on.
	 */
	protected $link;

	/**
	 * @var bool Whether or not the link has been updated.
	 */
	protected $linkUpdated;

	/**
	 * @param string $link URL the event was triggered on.
	 */
	public function __construct($link) {
		$this->link = $link;
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
		$this->linkUpdated = true;
	}

	/**
	 * Check if the link has been updated.
	 * 
	 * @return bool Whether or not the link has been updated.
	 */
	public function isLinkUpdated() {
		return $this->linkUpdated;
	}
}