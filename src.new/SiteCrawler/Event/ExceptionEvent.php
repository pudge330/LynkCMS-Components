<?php
namespace LynkCMS\Component\SiteCrawler\Event;

class ExceptionEvent extends AbstractException {
	const NAME = 'site-crawler.exception';
	protected $link;
	protected $exception;
	public function __construct($link, $exception) {
		$this->link = $link;
		$this->exception = $exception;
	}
	public function getLink() {
		return $this->link;
	}
	public function getException() {
		return $this->exception;
	}
}