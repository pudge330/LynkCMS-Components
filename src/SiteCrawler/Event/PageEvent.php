<?php
namespace BGStudios\Component\SiteCrawler\Event;

use Symfony\Component\DomCrawler\Crawler;

class PageEvent extends AbstractException {
	const NAME = 'site-crawler.page';
	protected $link;
	protected $response;
	protected $preventCrawl;
	public function __construct($link, $response) {
		$this->link = $link;
		$this->response = $response;
		$this->preventCrawl = false;
	}
	public function getLink() {
		return $this->link;
	}
	public function getResponse() {
		return $this->response;
	}
	public function getStatusCode() {
		return $this->response->geetStatusCode();
	}
	public function getDomCrawler() {
		return new Crawler($this->response->getContent());
	}
	public function preventCrawl() {
		$this->preventCrawl = true;
	}
	public function isCrawlPrevented() {
		return $this->preventCrawl;
	}
}