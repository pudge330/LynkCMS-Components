<?php
namespace BGStudios\Component\SiteCrawler\Event;

class RedirectEvent extends AbstractException {
	const NAME = 'site-crawler.redirect';
	protected $link;
	protected $redirectUrl;
	protected $response;
	protected $cancelRedirect;
	public function __construct($link, $redirectUrl, $response) {
		$this->link = $link;
		$this->redirectUrl = $redirectUrl;
		$this->response = $response;
		$this->cancelRedirect = false;
	}
	public function getLink() {
		return $this->link;
	}
	public function getRedirectUrl() {
		return $this->redirectUrl;
	}
	public function getResponse() {
		return $this->response;
	}
	public function getStatusCode() {
		return $this->response->getStatusCode();
	}
	public function cancelRedirect() {
		$this->cancelRedirect = true;
	}
	public function isRedirectCancelled() {
		return $this->cancelRedirect;
	}
}