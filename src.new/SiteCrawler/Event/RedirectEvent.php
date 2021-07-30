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

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Redirect response.
 */
class RedirectEvent extends AbstractException {

	/**
	 * string Event name.
	 */
	const NAME = 'site-crawler.redirect';

	/**
	 * @var string URL the event was triggered on.
	 */
	protected $link;

	/**
	 * @var string URL the page is attempting to redirect to.
	 */
	protected $redirectUrl;

	/**
	 * @var Symfony\Contracts\HttpClient\ResponseInterface The HTTP response.
	 */
	protected $response;

	/**
	 * @var bool Whether or not to cancel the redirect.
	 */
	protected $cancelRedirect;

	/**
	 * @param string $link URL the event was triggered on.
	 * @param string $redirectUrl URL the page is redirecting to.
	 * @param Symfony\Component\HttpClient\ResponseInterface $response The HTTP response.
	 */
	public function __construct($link, $redirectUrl, ResponseInterface $response) {
		$this->link = $link;
		$this->redirectUrl = $redirectUrl;
		$this->response = $response;
		$this->cancelRedirect = false;
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
	 * Get the redirect URL.
	 * 
	 * @return string The redirect URL.
	 */
	public function getRedirectUrl() {
		return $this->redirectUrl;
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
		return $this->response->getStatusCode();
	}

	/**
	 * Cancels the redirect.
	 */
	public function cancelRedirect() {
		$this->cancelRedirect = true;
	}

	/**
	 * Check if the redirect was canceled.
	 * 
	 * @return bool Whether or not the recirect is canceled.
	 */
	public function isRedirectCanceled() {
		return $this->cancelRedirect;
	}
}