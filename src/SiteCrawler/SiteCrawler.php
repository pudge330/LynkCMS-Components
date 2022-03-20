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

namespace Lynk\Component\SiteCrawler;

use Exception;
use Lynk\Component\SiteCrawler\Event;
use Lynk\Component\SiteCrawler\Link\LinkStorage;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient;

/**
 * Site crawler class that traverses through public links on a website.
 * Can be used to create sitemaps, crawl a website for indexing purposes, find HTTP errors among other uses.
 */
class SiteCrawler {

	/**
	 * @var string The websites domain name.
	 */
	protected $domain;

	/**
	 * @var string Temorary working directory, used for caching.
	 */
	protected $tmpDirectory;

	/**
	 * @var string Crawler instance unique ID.
	 */
	protected $instanceId;

	/**
	 * @var Array An array of file handles.
	 */
	protected $handles;

	/**
	 * @var Symfony\Component\HttpClient\HttpClient Instance of symfony's HttpClient class.
	 */
	protected $client;

	/**
	 * @var LinkStorage Links storage object.
	 */
	protected $crawledLinks;
	protected $uncrawledLinks;

	/**
	 * @var bool Whether or not to preserve the temporary files generate while crawling a site.
	 */
	protected $preserveTmp;

	/**
	 * @var Symfony\Component\EventDispatcher\EventDispatcher Instance of symfony's EvetnDispatcher class.
	 */
	protected $dispatcher;

	/**
	 * @param string $domain The website domain name.
	 * @param string $tmp Optional. The temporary working directory.
	 * @param Symfony\Component\EventDispatcher Optional. Instance of symfony's event dispatcher.
	 * @param bool $preserveTmp Optional. Whether or not to preserve the temp directory files.
	 * 
	 * @throws Exception
	 */
	public function __construct($domain, $tmp = null, $dispatcher = null, $preserveTmp = false) {
		if (!preg_match('/[a-zA-Z0-9-]{1,63}+\.[a-zA-Z0-9]{1,63}+$/', $domain)) {
			$this->throwException('Not a valid domain.');
		}
		$this->domain = $domain;
		// $this->options = array_merge(self::getDefaultOptions(), $options);
		$this->dispatcher = $dispatcher ?: new EventDispatcher();
		if ($tmp) {
			$this->tmpDirectory = preg_replace('/[\\/]$/', '', $tmp);
		}
		else if (is_writable(__DIR__)) {
			$this->tmpDirectory = __DIR__;
		}
		$this->instanceId = str_replace('.', '-', $domain) . '-' . date('YmdHis');
		if ($this->tmpDirectory) {
			$this->tmpDirectory .= '/sitecrawler-' . $this->instanceId;
		}
		mkdir($this->tmpDirectory, 0755, true);
		$this->handles = Array();
		$linkFolder = "{$this->tmpDirectory}/links";
		if (!file_exists($linkFolder)) {
			mkdir($linkFolder, 0755, true);
		}
		$this->crawledLinks = new LinkStorage($linkFolder, 'crawled');
		$this->uncrawledLinks = new LinkStorage($linkFolder, 'unvisited');
		$this->preserveTmp = $preserveTmp;
	}

	/**
	 * Deconstruct the onject. Clean temporary directory if necessary and close file handles.
	 */
	public function __destruct() {
		//--remove tmp directory
		if (is_dir($this->tmpDirectory) && !$this->preserveTmp) {
			exec("rm -rf {$this->tmpDirectory}");
		}
		foreach ($this->handles as $k => $h) {
			fclose($h);
			unset($this->handles[$k]);
		}
	}

	/**
	 * Return the event dispatcher.
	 * 
	 * @return Symfony\Component\EventDispatcher\EventDispatcher The event Dispatcher.
	 */
	public function getEventDispatcher() {
		return $this->dispatcher;
	}

	/**
	 * Traverse a URL.
	 * 
	 * @param string $url Optional. The URL path, defaults to / or the home page.
	 * @param Array $options Optional. Crawler options.
	 *                                 - file:             A file to store valid status 200 URLs in.
	 *                                 - logFolder:        Logging folder within the temporary directory.
	 *                                 - subdomain:        Subdomain to limit the scope of the crawling or for the starting position.
	 *                                 - domainScope:      Scope of the crawl, 'domain' includes TLD and all subdmains, 'subdomain' limits it to provided subdomain.
	 *                                 - scheme:           URL scheme. http or https.
	 *                                 - schemeScope:      Limits crawling to a particular scheme, http or https.
	 *                                 - maxRedirectCount: Max redirect follows before giving up.
	 *                                 - depth:            Max depth or 'parts' or a url path.
	 *                                 - userAgent:        User Agent to send to server with requests. Defaults to FireFox 72.
	 * 
	 * @return Array An array of links from website.
	 */
	public function traverse($url = '/', $options = Array()) {
		$options = array_merge(Array(
			'file' => null,
			'logFolder' => null,
			'subdomain' => null,
			'domainScope' => 'domain', //--domain,subdomain
			'scheme' => 'https',
			'schemeScope' => 'both', //--both,http,https
			'maxRedirectCount' => 20,
			'depth' => null,
			'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:72.0) Gecko/20100101 Firefox/72.0'
		), $options);

		if ($options['logFolder']) {
			$options['logFolder'] = "{$this->tmpDirectory}/{$options['logFolder']}";
		}

		if ($options['domainScope'] === 'subdomain' && !$options['subdomain']) {
			$this->log($options['logFolder'], 'general', "Domain scope set to 'subdomain' but the subdomain was not provided");
			$this->log($options['logFolder'], 'general', "Switching domain scope to 'domain'");
			$options['domainScope'] = 'domain';
		}

		if ($options['schemeScope'] !== 'both' && ($options['scheme'] !== $options['schemeScope'])) {
			$this->log($options['logFolder'], 'general', "Scheme scope set to '{$options['schemeScope']}' but the scheme was set to {$options['scheme']}");
			$this->log($options['logFolder'], 'general', "Switching scheme scope to '{$options['scheme']}'");
			$options['schemeScope'] = $options['scheme'];
		}

		if ($options['logFolder']) {
			$options['logFolder'] = preg_replace('/[\\/]$/', '', $options['logFolder']);
		}

		$entrySubdomain = $options['subdomain'] ? "{$options['subdomain']}." : '';
		$entryPoint = "{$options['scheme']}://{$entrySubdomain}{$this->domain}{$url}";

		$this->client = HttpClient\HttpClient::create([
			'headers' => [
				'User-Agent' => $options['userAgent']
			]
		]);
		
		$return = $this->traverseUrl($entryPoint, $options);
		return $return;
	}

	/**
	 * Recursive function to traverses a URL/website.
	 * 
	 * @param string $url Optional. The URL path, defaults to / or the home page.
	 * @param Array $options Optional. Crawler options.
	 *                                 - file:             A file to store valid status 200 URLs in.
	 *                                 - logFolder:        Logging folder within the temporary directory.
	 *                                 - subdomain:        Subdomain to limit the scope of the crawling or for the starting position.
	 *                                 - domainScope:      Scope of the crawl, 'domain' includes TLD and all subdmains, 'subdomain' limits it to provided subdomain.
	 *                                 - scheme:           URL scheme. http or https.
	 *                                 - schemeScope:      Limits crawling to a particular scheme, http or https.
	 *                                 - maxRedirectCount: Max redirect follows before giving up.
	 *                                 - depth:            Max depth or 'parts' or a url path.
	 *                                 - userAgent:        User Agent to send to server with requests. Defaults to FireFox 72.
	 * @param Array $links An array of found links.
	 * @param int $redirectCount Current redirect follow count.
	 * 
	 * @return Array An array of links from website.
	 */
	protected function traverseUrl($url, $options, $links = Array(), $redirectCount = 0) {
		$furl = $html = null;
		try {
			$chosenUrl = null;
			do {
				$linkEvent = new Event\LinkEvent($url);
				$this->dispatcher->dispatch(Event\LinkEvent::NAME, $linkEvent);
				if ($linkEvent->isSiteCrawlerStopped()) {
					return $links;
				}
				else {
					$url = $linkEvent->getLink();
				}
				if ($url && !$linkEvent->isRequestPrevented()) {
					$chosenUrl = $url;
				}
				else {
					if ($url) {
						$this->crawledLinks->set($url);
					}
					do {
						$url = $this->uncrawledLinks->get();
					} while ($url && $this->crawledLinks->has($url));
				}
			} while (!$chosenUrl && $url);

			if (!$chosenUrl) {
				return $links;
			}
			$url = $chosenUrl;

			$this->log($options['logFolder'], 'general', "Crawling: {$url}");

			$this->crawledLinks->set($url);

			/*
				@--https://symfony.com/doc/4.4/components/http_client.html#handling-exceptions
				When the HTTP status code of the response is in the 300-599 range (i.e. 3xx, 4xx or 5xx) your code is expected to handle it. If you don't do that, the getHeaders() and getContent() methods throw an appropriate exception
			*/
			$response = $this->client->request('GET', $url, [
				'max_redirects' => 0 //--we want to manually handle redirects
			]);

			$finalUrlParts = $this->parseUrl($url);

			if ($response->getStatusCode() == 200) {
				if ($options['file']) {
					file_put_contents($options['file'], "{$url}\n", FILE_APPEND);
				}
				else {
					$links[] = $url;
				}
				$redirectCount = 0;
				$pageEvent = new Event\PageEvent($url, $response);
				$this->dispatcher->dispatch(Event\PageEvent::NAME, $pageEvent);

				if ($pageEvent->isSiteCrawlerStopped()) {
					return $links;
				}

				if (!$pageEvent->isCrawlPrevented()) {
					$crawler = new Crawler($response->getContent());
					$linkElements = $crawler->filter('a');
					foreach ($linkElements as $domElement) {
						/*
							Triming dots for now, will want to have it correct the url based on current page location in future.
						*/
						$ohref = $href = trim($domElement->getAttribute('href'), '.');
						$hrefParts = $this->parseUrl($href);
						if ($hrefParts['host'] == '') {
							$hrefParts = $this->mergeUrlParts($finalUrlParts, $hrefParts);
						}
						if ($hrefParts['path'] == '')
							$hrefParts['path'] = '/';
						$href = $this->buildUrl($hrefParts);
						if ($hrefParts) {
							$schemePass = false;
							if ($hrefParts['scheme'] == '' || $options['schemeScope'] == 'both')
								$schemePass = true;
							else if ($hrefParts['scheme'] == $options['scheme'])
								$schemePass = true;
							$domainPass = false;
							if ($hrefParts['domain'] == '' || $hrefParts['domain'] == $this->domain)
								$domainPass = true;
							$subdomainPass = true;
							if ($options['domainScope'] == 'subdomain' && $hrefParts['subdomain'] != $options['subdomain'])
								$subdomainPass = false;
							$depthPass = false;
							if ($options['depth'] === null || sizeof($hrefParts['pathParts']) <= $options['depth'])
								$depthPass = true;
							if ($hrefParts['host'] == '')
								$hrefParts['host'] = $this->domain;
							if ($schemePass && $domainPass && $subdomainPass && $depthPass) {
								if (!$this->crawledLinks->has($href) && !$this->uncrawledLinks->has($href)) {
									$this->uncrawledLinks->set($href);
								}
							}
							else {
								$unparsableLinkEvent = new Event\UnparsableLinkEvent($href);
								$this->dispatcher->dispatch(Event\UnparsableLinkEvent::NAME, $unparsableLinkEvent);

								if ($unparsableLinkEvent->isSiteCrawlerStopped()) {
									return $links;
								}

								$updatedLink = $unparsableLinkEvent->getLink();
								if ($updatedLink && $updatedLink !== $href && !$this->crawledLinks->has($updatedLink) && !$this->uncrawledLinks->has($href)) {
									$this->uncrawledLinks->set($updatedLink);
								}
							}
						}
					}
				}
			}
			else if (in_array($response->getStatusCode(), [301, 302, 307])) {
				$redirectingTo = isset($response->getInfo()['redirect_url']) ? $response->getInfo()['redirect_url'] : null;
				if ($redirectingTo) {
					$hrefParts = $this->parseUrl($redirectingTo);
					if ($hrefParts) {
						if ($hrefParts['host'] == '') {
							$hrefParts = $this->mergeUrlParts($finalUrlParts, $hrefParts);
						}
						if ($hrefParts['path'] == '')
							$hrefParts['path'] = '/';
						$redirectingTo = $this->buildUrl($hrefParts);
					}
					if (!$this->crawledLinks->has($redirectingTo) && !$this->uncrawledLinks->has($redirectingTo) && $redirectCount < $options['maxRedirectCount']) {
						$redirectEvent = new Event\RedirectEvent($url, $redirectingTo, $response);
						$this->dispatcher->dispatch(Event\RedirectEvent::NAME, $redirectEvent);
						if ($redirectEvent->isSiteCrawlerStopped()) {
							return $links;
						}
						if (!$redirectEvent->isRedirectCancelled()) {
							$this->uncrawledLinks->set($redirectingTo);
							$redirectCount++;
						}
						else {
							$redirectCount = 0;
						}
					}
				}
			}
		}
		catch (Exception $e) {
			$exceptionEvent = new Event\ExceptionEvent($url, $e);
			$this->dispatcher->dispatch(Event\ExceptionEvent::NAME, $exceptionEvent);
			if ($exceptionEvent->isSiteCrawlerStopped()) {
				return $links;
			}
		}

		$url = $this->uncrawledLinks->get();
		if ($url) {
			if ($options['delay'] && is_numeric($options['delay'])) {
				sleep((int)$options['delay']);
			}
			$links = $this->traverseUrl($url, $options, $links, $redirectCount);
		}

		return $links;
	}

	/**
	 * Log a message to a file.
	 * 
	 * @param string $root The log folder.
	 * @param string $log The name of the log file.
	 * @param string $message The message to log.
	 * 
	 * @throws Exception
	 */
	protected function log($root, $log, $message) {
		if ($root && !file_exists($root)) {
			if (!mkdir($root, 0755, true)) {
				$this->throwException('logFolder did not exist and cannot be created');
			}
		}
		if ($root && in_array($log, Array('general', 'error', 'redirect'))) {
			$log = $log == 'general' ? 'sitecrawler' : $log;
			$key = "{$log}.log";
			if (!$this->handles[$key]) {
				$this->handles[$key] = fopen($root . "/{$log}.log", 'w+');
			}
			if (is_array($message)) {
				$message = $this->writeCsvLine($message);
			}
			fwrite($this->handles[$key], $message . "\n");
		}
	}

	/**
	 * Write/generate a CSV line of data.
	 * 
	 * @param Array $data The data to write.
	 * 
	 * @return string The CSV line.
	 */
	public function writeCsvLine($data) {
		$h = fopen('php://temp', 'r+');
		fputcsv($h, $data, ',', '"');
		rewind($h);
		$data = fread($h, 1048576);
		fclose($h);
		return trim($data);
	}

	/**
	 * Get the website domain.
	 * 
	 * @return string The domain name.
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * Throw Exception.
	 * 
	 * @throws Exception
	 */
	protected function throwException($msg) {
		throw new Exception('SiteCrawler Exception: ' . $msg);
	}

	/**
	 * Merge url parts arrays.
	 * 
	 * @param Array $org Original set of URL parts.
	 * @param Array $new New set of URL parts.
	 * 
	 * @param Array Combined set of URL parts.
	 */
	protected function mergeUrlParts($org, $new) {
		if ($new['host'] == '') {
			$new['host'] = $org['host'];
			$new['domain'] = $org['domain'];
			$new['subdomain'] = $org['subdomain'];
		}
		if ($new['scheme'] == '')
			$new['scheme'] = $org['scheme'];
		return $new;
	}

	/**
	 * Parse a URL into an arry of parts.
	 * 
	 * @param string $url The URL to parse.
	 * @param string $path Optional. The URL path.
	 */
	public function parseUrl($url, $path = '') {
		$regexes = [
			'scheme' => '/^([a-zA-Z0-9]+):\/\/|^\/\//'
			,'username-password' => '/^([a-zA-Z0-9_-]+)?:([a-zA-Z0-9_-]+)?@/'
			,'host' => '/^((?:(?:[a-zA-Z0-9_-]+)\.)+(?:[a-zA-Z0-9_-]+){1})/'
			,'port' => '/^(?::([0-9]+))/'
			,'path' => '/^((?!\/\/)(?:\/|\.\/|\.\.\/)(?:[^?#]+)?)/'
			,'query' => '/^(?:\?([^#]+)*)/'
			,'fragment' => '/^(?:#(.+)*)/'
		];
		$tmpUrl = $url;
		$urlParts = ['url' => $url,'scheme' => '','username' => '','password' => '','subdomain' => '','domain' => '','host' => '','port' => '','path' => '','query' => '','fragment' => '','queryString' => '','pathParts' => [], 'queryVariables' => []];
		$has = [];
		foreach ($regexes as $regexKey => $regex) {
			$matches = [];
			$has[$regexKey] = false;
			if (preg_match($regex, $tmpUrl, $matches)) {
				$has[$regexKey] = true;
				$keys = explode('-', $regexKey);
				for ($i = 0; $i < sizeof($keys); $i++) {
					if (isset($matches[$i + 1]))
						$urlParts[$keys[$i]] = $matches[$i + 1];
				}
				$tmpUrl = substr($tmpUrl, strlen($matches[0]));
			}
		}
		$pass = false;
		foreach ($urlParts as $partKey => $partVal) {
			if ($partVal != '')
				$pass = true;
		}
		if ($urlParts['host'] == '' && $has['scheme'])
			$pass = false;
		else if ($urlParts['host'] == '') {
			if ($urlParts['path'] == '')
				$pass = false;
			else {
				if ($urlParts['scheme'] != '' || $urlParts['username'] != '' || $urlParts['password'] != '' || $urlParts['host'] != '' || $urlParts['port'] != '')
					$pass = false;
			}
		}
		else if ($urlParts['path'] == '')
			$urlParts['path'] = $path;
		if ($pass) {
			//--get 'domain' and 'subdomain' from 'host'
			if ($urlParts['host'] != '') {
				$hostBits = explode('.', $urlParts['host']);
				$hostBitsSize = sizeof($hostBits);
				if (sizeof($hostBits) > 2) {
					$urlParts['domain'] = "{$hostBits[$hostBitsSize - 2]}.{$hostBits[$hostBitsSize - 1]}";
					array_pop($hostBits);array_pop($hostBits);
					$urlParts['subdomain'] = implode('.', $hostBits);
				}
				else {
					$urlParts['domain'] = $urlParts['host'];
				}
			}
			$tmpPath = preg_replace('/^\/+|\/+$/', "", $urlParts['path']);
			//--get query parts
			if ($tmpPath != '') {
				$urlParts['pathParts'] = explode('/', $tmpPath);
			}
			else
				$urlParts['pathParts'] = ['/'];
			$urlParts['queryString'] = $urlParts['path'] . ($urlParts['query'] != '' ? "?{$urlParts['query']}" : '');
			if ($urlParts['query'] != '') {
				parse_str($urlParts['query'], $urlParts['queryVariables']);
			}
			if (in_array($urlParts['username'], array('mailto')))
				return null;
			else
				return $urlParts;
		}
		else
			return null;
	}

	/**
	 * Build URL from parts array.
	 * 
	 * @param Array $parts The parts of a URL.
	 * 
	 * @return string The URL.
	 */
	public function buildUrl($parts) {
		$parts = array_merge(array(
			'scheme' => ''
			,'username' => ''
			,'password' => ''
			,'subdomain' => ''
			,'domain' => ''
			,'host' => ''
			,'port' => ''
			,'path' => ''
			,'query' => ''
			,'fragment' => ''
		), $parts);
		if (isset($parts['scheme']) && $parts['scheme'] != '')
			$parts['scheme'] = "{$parts['scheme']}://";
		if (($parts['domain'] != '' || $parts['host'] != '') && $parts['scheme'] == '')
			$parts['scheme'] = '//';
		if ($parts['host'] == '') {
			if ($parts['domain'] != '')
				$parts['host'] = $parts['domain'];
			if ($parts['subdomain'] != '' && $parts['domain'] != '')
				$parts['host'] = "{$parts['subdomain']}.{$parts['host']}";
		}
		$parts['auth'] = '';
		if ($parts['username'] != '' || $parts['password'] != '')
			$parts['auth'] = "{$parts['username']}:{$parts['password']}@";
		if ($parts['port'] != '')
			$parts['port'] = ":{$parts['port']}";
		if ($parts['query'] != '')
			$parts['query'] = "?{$parts['query']}";
		if ($parts['fragment'] != '')
			$parts['fragment'] = "#{$parts['fragment']}";
		if (preg_match('/^\./', $parts['path']))
			$parts['path'] = "/{$parts['path']}";
		if ($parts['host'] != '')
			return "{$parts['scheme']}{$parts['auth']}{$parts['host']}{$parts['port']}{$parts['path']}{$parts['query']}{$parts['fragment']}";
		else {
			if ($parts['path'] == '')
				$parts['path'] = '/';
			return "{$parts['path']}{$parts['query']}{$parts['fragment']}";
		}
	}
}
