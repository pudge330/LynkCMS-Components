<?php
namespace BGStudios\Component\SiteCrawler\Event;

use Symfony\Component\EventDispatcher\Event;

class AbstractException extends Event {
	protected $siteCrawlStopped = false;
	public function stopSiteCrawler() {
		$this->siteCrawlStopped = true;
	}
	public function isSiteCrawlerStopped() {
		return $this->siteCrawlStopped;
	}
}