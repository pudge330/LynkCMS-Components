<?php
namespace BGStudios\Component\SiteCrawler\Event;

class LinkEvent extends AbstractException {
	const NAME = 'site-crawler.link';
	protected $link;
	protected $preventRequest;
	public function __construct($link) {
		$this->link = $link;
		$this->preventRequest = false;
	}
	public function getLink() {
		return $this->link;
	}
	public function setLink($link) {
		$this->link = $link;
	}
	public function preventRequest() {
		$this->preventRequest = true;
	}
	public function isRequestPrevented() {
		return $this->preventRequest;
	}
}