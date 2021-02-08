<?php
namespace BGStudios\Component\SiteCrawler\Event;

class UnparsableLinkEvent extends AbstractException {
	const NAME = 'site-crawler.unparsable-link';
	protected $link;
	protected $linkUpdated;
	public function __construct($link) {
		$this->link = $link;
	}
	public function getLink() {
		return $this->link;
	}
	public function setLink($link) {
		$this->link = $link;
		$this->linkUpdated = true;
	}
}