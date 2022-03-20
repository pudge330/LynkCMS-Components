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

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Page event.
 */
class PageEvent extends AbstractException {

	/**
	 * @var string Event name.
	 */
	const NAME = 'site-crawler.page';

	/**
	 * @var string URL the event was triggered on.
	 */
	protected $link;

	/**
	 * @var Symfony\Contracts\HttpClient\ResponseInterface The HTTP response.
	 */
	protected $response;

	/**
	 * @var bool Whether or not to prevent page crawling.
	 */
	protected $preventCrawl;

	/**
	 * @param string $link URL the event was triggered on.
	 * @param Symfony\Component\HttpClient\ResponseInterface $response The HTTP response.
	 */
	public function __construct($link, ResponseInterface $response) {
		$this->link = $link;
		$this->response = $response;
		$this->preventCrawl = false;
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
	 * Get the response.
	 * 
	 * @return Symfony\Component\HttpClient\ResponseInterface The response.
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Get response status code.
	 * 
	 * @return int The response status code.
	 */
	public function getStatusCode() {
		return $this->response->geetStatusCode();
	}

	/**
	 * Get instance of Crawler for the current response content.
	 * 
	 * @return Symfony\Component\DomCrawler\Crawler The dom crawler.
	 */
	public function getDomCrawler() {
		return new Crawler($this->response->getContent());
	}

	/**
	 * Prevent crawling of responsee content.
	 */
	public function preventCrawl() {
		$this->preventCrawl = true;
	}

	/**
	 * Check if crawling is prevented.
	 * 
	 * @return bool Whether or not the crawling is prevented.
	 */
	public function isCrawlPrevented() {
		return $this->preventCrawl;
	}
}