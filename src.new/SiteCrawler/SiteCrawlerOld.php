<?php
namespace LynkCMS\Component\SiteCrawler;

use Exception;
use Symfony\Component\DomCrawler\Crawler;

class SiteCrawlerOld {
	//--new variables
	private static $defaultOptions = array(
		'depth' => 5
		,'depthMap' => ['1.0','0.75','0.50']
		,'scheme' => 'both' //--both,http,https
		,'entryScheme' => 'http'
		,'entryQueryString' => '/'
		,'baseScheme' => 'http'
		,'subdomain' => null
		,'scope' => 'domain' //--domain,subdomain
		,'followRedirects' => true
		,'redirectFollowMaxCount' => 5
		,'trimProceedingDots' => true
		,'logDetails' => true
		,'logRedirects' => true
		,'logErrors' => true
		,'logFolder' => null
	);
	private $options = array();
	private $domain;
	private $tmpStoragePath;
	private $logStoragePath;
	private $logHandles;
	private $fileHandle;
	private $traverseReturn;
	//--traverse return options
	const RETURN_ARRAY = 1;
	const RETURN_FILEPATH = 2;

	public function __construct($domain, $tmp, $opts = array()) {
		$this->domain = $domain;
		$this->options = array_merge(static::$defaultOptions, $opts);
		if (!preg_match('/[a-zA-Z0-9-]{1,63}+\.[a-zA-Z0-9]{1,63}+$/', $this->domain)) {
			$this->throwException('Not a valid domain.');
		}
		if (!file_exists($tmp) || !is_writable($tmp)) {
			$this->throwException('Temporary storage directory must already exists and be writable.');
		}
		$this->tmpStoragePath = rtrim(rtrim($tmp, '\\'), '/');
		$this->logStoragePath = $this->tmpStoragePath . '/' . ($this->options['logFolder'] ? $this->options['logFolder'] : 'logs') . '/';
		if (!file_exists($this->logStoragePath))
			mkdir($this->logStoragePath, 0755, true);
		$this->logHandles = array('sitemap' => null, 'sitemap-traverse' => null, 'redirects' => null, 'errors' => null);
		$this->fileHandle = null;
	}

	public function __destruct() {
		foreach ($this->logHandles as $logHandleKey => $logHandle) {
			if ($logHandle) {
				fclose($logHandle);
			}
		}
	}

	public function writeLog($log, $message, $context = null) {
		if ($context !== null) {
			if (!is_string($context))
				$context = json_encode($context);
			$context = " | {$context}";
		}
		$dt = date('H:i:s');
		if (in_array($log, array('sitemap', 'sitemap-traverse')) && !$this->options['logDetails'])
			return;
		if ($log == 'redirects' && !$this->options['logRedirects'])
			return;
		if ($log == 'errors' && !$this->options['logErrors'])
			return;
		if (array_key_exists($log, $this->logHandles) && !$this->logHandles[$log]) {
			$this->logHandles[$log] = fopen("{$this->logStoragePath}{$log}.log", 'w');
			fwrite($this->logHandles[$log], "** Log Created: " . date('F j, Y H:i:s') . "**\n\n");
			fwrite($this->logHandles[$log], "{$dt} | {$message}{$context}\n\n");
		}
		else if (array_key_exists($log, $this->logHandles)) {
			fwrite($this->logHandles[$log], "{$dt} | {$message}{$context}\n\n");
		}
	}

	public static function getDefaultOptions() {
		return static::$defaultOptions;
	}

	public static function getDefaultOption($key) {
		if (isset(static::$defaultOptions[$key]))
			return static::$defaultOptions[$key];
	}

	public function throwException($msg) {
		throw new Exception('SitemapGenerator Error: ' . $msg);
	}

	public static function getOptions() {
		return $this->options;
	}

	public static function setOptions($opts) {
		$this->options = array_merge($this->options, $opts);
	}

	public static function getOption($key) {
		if (isset($this->options[$key]))
			return $this->options[$key];
	}

	public static function setOption($key, $val) {
		$this->options[$key] = $val;
	}

	public function getBaseUrl() {
		return "{$this->options['baseScheme']}://{$this->getDomain()}";
	}

	public function getDomain() {
		return $this->domain;
	}

	public function traverse($filter = null, $returnType = null) {
		if (!$returnType)
			$returnType = self::RETURN_ARRAY;
		//--determine starting point
		$entryPointSubDomain = $this->options['scope'] == 'subdomain' && $this->options['subdomain'] ? "{$this->options['subdomain']}." : '';
		$entryPointQueryString = $this->options['entryQueryString'] ? $this->options['entryQueryString'] : '/';
		$entryPoint = "{$this->options['entryScheme']}://{$entryPointSubDomain}{$this->domain}{$entryPointQueryString}";
		$this->writeLog('sitemap', '- Traverse Started', $entryPoint);
		switch ($returnType) {
			case self::RETURN_FILEPATH:
				$this->traverseReturn = self::RETURN_FILEPATH;
				//--create handle
				$fileName = $this->tmpStoragePath . '/traverse-links-' . hash('sha1', microtime());
				$this->fileHandle = fopen($fileName, 'w');
				$links = array();
				$this->traverseUrl($entryPoint, $links, $filter);
				fclose($this->fileHandle);
				$this->writeLog('sitemap', '- Traverse Done', sizeof($links));
				return $fileName;
				break;
			case self::RETURN_ARRAY:
			default:
				$this->traverseReturn = self::RETURN_ARRAY;
				$links = array();
				$links = $this->traverseUrl($entryPoint, $links, $filter);
				sort($links);
				$this->writeLog('sitemap', '- Traverse Done', sizeof($links));
				return $links;
				break;
		}
		return array();
	}

	public function traverseSite($returnType = null) {
		if (!$returnType)
			$returnType = self::RETURN_ARRAY;
		$entryPointSubDomain = $this->options['scope'] == 'subdomain' && $this->options['subdomain'] ? "{$this->options['subdomain']}." : '';
		$entryPointQueryString = $this->options['entryQueryString'] ? $this->options['entryQueryString'] : '/';
		$entryPoint = "{$this->options['entryScheme']}://{$entryPointSubDomain}{$this->domain}{$entryPointQueryString}";
		$this->writeLog('sitemap', '- Traverse Started', $entryPoint);
		switch ($returnType) {
			case self::RETURN_FILEPATH:
				$this->traverseReturn = self::RETURN_FILEPATH;
				//--create handle
				$fileName = $this->tmpStoragePath . '/traverse-links-' . hash('sha1', microtime());
				$this->fileHandle = fopen($fileName, 'w');
				$links = array();
				$this->traversePage($entryPoint, $links, $filter);
				fclose($this->fileHandle);
				$this->writeLog('sitemap', '- Traverse Done', sizeof($links));
				return $fileName;
				break;
			case self::RETURN_ARRAY:
			default:
				$this->traverseReturn = self::RETURN_ARRAY;
				$links = array();
				$links = $this->traversePage($entryPoint, $links, $filter);
				sort($links);
				$this->writeLog('sitemap', '- Traverse Done', sizeof($links));
				return $links;
				break;
		}
		return array();
	}

	private function traversePage($url, array &$links = array()) {
		list($finalUrl, $html) = $this->getUrlContents($url);
		if ($html)
			$finalUrlParts = $this->parseUrl($finalUrl);
		$this->writeLog('sitemap-traverse', "{$url} -- {$finalUrl}", sizeof($links));
		if ($html && !in_array($finalUrl, $links)) {
			$links[] = $finalUrl;
			if ($this->traverseReturn == self::RETURN_FILEPATH) {
				$padding = '';
				if (sizeof($links) > 1)
					$padding = "\n";
				fwrite($this->fileHandle, "{$padding}{$finalUrl}");
			}
			$crawler = new Crawler($html);
			$domElements = $crawler->filter('a');
			foreach ($domElements as $domElement) {
				if ($this->options['trimProceedingDots'])
					$ohref = $href = ltrim($domElement->getAttribute('href'), '.');
				else
					$ohref = $href = $domElement->getAttribute('href');
				$this->writeLog('sitemap', 'Url Found', $href);
				$hrefParts = $this->parseUrl($href);
				if ($hrefParts['host'] == '') {
					$hrefParts = $this->mergeUrlParts($finalUrlParts, $hrefParts);
				}
				if ($hrefParts['path'] == '')
					$hrefParts['path'] = '/';
				$href = $this->buildUrl($hrefParts);
				if ($hrefParts) {
					//--scheme, domain, subdomain match?
					$schemePass = false;
					if ($hrefParts['scheme'] == '' || $this->options['scheme'] == 'both')
						$schemePass = true;
					else if ($hrefParts['scheme'] == $this->options['scheme'])
						$schemePass = true;
					$domainPass = false;
					if ($hrefParts['domain'] == '' || $hrefParts['domain'] == $this->domain)
						$domainPass = true;
					$subdomainPass = true;
					if ($this->options['scope'] == 'subdomain' && $hrefParts['subdomain'] != $this->options['subdomain'])
						$subdomainPass = false;
					if ($hrefParts['host'] == '')
						$hrefParts['host'] = $this->domain;
					if ($schemePass && $domainPass && $subdomainPass) {
						if (!in_array($href, $links) && sizeof($hrefParts['pathParts']) <= $this->options['depth']) {
							$filteredResult = null;
							if ($filter) {
								$filteredResult = call_user_func_array($filter, array($ohref, $hrefParts));
							}
							if ($filteredResult !== false) {
								$links = $this->traverseUrl($href, $links, $filter);
							}
						}
					}
					else {
					}
				}
			}
		}
		return $links;
	}

	private function traverseUrl($url, array &$links = array(), $filter = null) {
		list($finalUrl, $html) = $this->getUrlContents($url);
		if ($html)
			$finalUrlParts = $this->parseUrl($finalUrl);
		$this->writeLog('sitemap-traverse', "{$url} -- {$finalUrl}", sizeof($links));
		if ($html && !in_array($finalUrl, $links)) {
			$links[] = $finalUrl;
			if ($this->traverseReturn == self::RETURN_FILEPATH) {
				$padding = '';
				if (sizeof($links) > 1)
					$padding = "\n";
				fwrite($this->fileHandle, "{$padding}{$finalUrl}");
			}
			$crawler = new Crawler($html);
			$domElements = $crawler->filter('a');
			foreach ($domElements as $domElement) {
				if ($this->options['trimProceedingDots'])
					$ohref = $href = ltrim($domElement->getAttribute('href'), '.');
				else
					$ohref = $href = $domElement->getAttribute('href');
				$this->writeLog('sitemap', 'Url Found', $href);
				$hrefParts = $this->parseUrl($href);
				if ($hrefParts['host'] == '') {
					$hrefParts = $this->mergeUrlParts($finalUrlParts, $hrefParts);
				}
				if ($hrefParts['path'] == '')
					$hrefParts['path'] = '/';
				$href = $this->buildUrl($hrefParts);
				if ($hrefParts) {
					//--scheme, domain, subdomain match?
					$schemePass = false;
					if ($hrefParts['scheme'] == '' || $this->options['scheme'] == 'both')
						$schemePass = true;
					else if ($hrefParts['scheme'] == $this->options['scheme'])
						$schemePass = true;
					$domainPass = false;
					if ($hrefParts['domain'] == '' || $hrefParts['domain'] == $this->domain)
						$domainPass = true;
					$subdomainPass = true;
					if ($this->options['scope'] == 'subdomain' && $hrefParts['subdomain'] != $this->options['subdomain'])
						$subdomainPass = false;
					if ($hrefParts['host'] == '')
						$hrefParts['host'] = $this->domain;
					if ($schemePass && $domainPass && $subdomainPass) {
						if (!in_array($href, $links) && sizeof($hrefParts['pathParts']) <= $this->options['depth']) {
							$filteredResult = null;
							if ($filter) {
								$filteredResult = call_user_func_array($filter, array($ohref, $hrefParts));
							}
							if ($filteredResult !== false) {
								$links = $this->traverseUrl($href, $links, $filter);
							}
						}
					}
					else {
					}
				}
			}
		}
		return $links;
	}

	public function determinePriority($url, $depthMap = null) {
		$depth = $this->urlDepth($url);
		if (!$depthMap)
			$depthMap = $this->options['depthMap'];
		if ($depthMap && is_array($depthMap)) {
			$mapSize = sizeof($depthMap);
			if ($depth < $mapSize - 1)
				return $depthMap[$depth - 1];
			else if ($mapSize > 0)
				return $depthMap[$mapSize - 1];
			else
				return '0.5';
		}
		else
			return '0.5';
	}

	public function build($path, $links, $modifier = null) {
		if (!file_exists(dirname($path)))
			mkdir(dirname($path), 755, true);
		$nodesCreated = 0;
		if (is_array($links)) {
			$smhandle = fopen($path, 'w');
			foreach ($links as $link) {
				$nodes = array(
					'priority' => $this->determinePriority($link)
					,'changefreq' => null
					,'lastmod' => null
				);
				$modifierResult = null;
				if ($modifier)
					$modifierResult = call_user_func_array($modifier, array($link, $this->parseUrl($link), &$nodes));
				if ($modifierResult !== false) {
					$nodesCreated++;
				}
				if ($nodesCreated == 1) {
					fwrite($smhandle,
						"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
						"<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
					);
				}
				fwrite($smhandle, $this->buildNodeUrl($link, $nodes));
			}
			if ($nodesCreated > 0) {
				fwrite($smhandle, "</urlset>");
			}
			fclose($smhandle);
		}
		else {
			if (file_exists($links)) {
				$fhandle = fopen($links, 'r');
				$smhandle = fopen($path, 'w');
				while (($line = fgets($fhandle)) !== false) {
					$nodes = array(
						'priority' => $this->determinePriority($line)
						,'changefreq' => null
						,'lastmod' => null
					);
					$modifierResult = null;
					if ($modifier)
						$modifierResult = call_user_func_array($modifier, array($line, $this->parseUrl($line), &$nodes));
					if ($modifierResult !== false) {
						$nodesCreated++;
						if ($nodesCreated == 1) {
							fwrite($smhandle,
								"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
								"<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
							);
						}
						fwrite($smhandle, $this->buildNodeUrl($line, $nodes));
					}
				}
				if ($nodesCreated > 0) {
					fwrite($smhandle, "</urlset>");
				}
				fclose($smhandle);
				fclose($fhandle);
			}
		}
		return $nodesCreated > 0 ? $nodesCreated : null;
	}

	private function buildNodeUrl($link, $nodes) {
		$urlNode .= "\t<url>\n";
		$urlNode .= "\t\t<loc>".trim(htmlspecialchars($link))."</loc>\n";
		if ($nodes['priority'])
			$urlNode .= "\t\t<priority>".trim(htmlspecialchars($nodes['priority']))."</priority>\n";
		if ($nodes['lastmod'])
			$urlNode .= "\t\t<lastmod>".trim(htmlspecialchars($nodes['lastmod']))."</lastmod>\n";
		if ($nodes['changefreq'])
			$urlNode .= "\t\t<changefreq>".trim(htmlspecialchars($nodes['changefreq']))."</changefreq>\n";
		$urlNode .= "\t</url>\n";
		return $urlNode;
	}

	private function getUrlContents($url, $redirectFollowCount = 0, $visitedLinks = array(), $redirectCode = null, $originalUrl = null) {
		if (is_callable('curl_init')) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
			$data = curl_exec($curl);
			//--get headers and parse out Location|URI is present
			$locationMatches = array();
			preg_match("/(?:\r\n|\n)(?:Location|URI): *(.*?) *(?:\r\n|\n)/", $data, $locationMatches);
			$newLocation = null;
			if (sizeof($locationMatches) > 0)
				$newLocation = $locationMatches[1];
			$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$headers = substr($data, 0, $headerSize);
			$data = substr($data, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
			$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$lastUrl = $url;
			curl_close($curl);
			$data = str_replace("\r\n", "\n", $data);
			$urlParts = $this->parseUrl($url);
			$this->writeLog('sitemap', "HTTP Url/LastUrl", [$httpCode, $url, $newLocation]);
			if (!in_array($httpCode, array(301,302,307,200)))
				$this->writeLog('errors', "HTTP Error", [$httpCode, $url]);
			if (strpos(strtolower($contentType), 'text/html') !== false) {
				if (in_array($httpCode, array(301,302,307)) && $redirectFollowCount < $this->options['redirectFollowMaxCount']) {
					if ($this->options['followRedirects'] && $httpCode == 301) { //--permanent redirect
						$this->writeLog('redirects', "Permanent Redirect", [$url, $newLocation]);
						if (!in_array($url, $visitedLinks)) {
							$visitedLinks[] = $url;
							if (!$redirectCode)
								$redirectCode = $httpCode;
							if (!$originalUrl)
								$originalUrl = $newLocation;
							$newLocationParts = $this->parseUrl($newLocation);
							if ($newLocationParts && $newLocationParts['host'] == '')
								$newLocationParts = $this->mergeUrlParts($urlParts, $newLocationParts);
							if ($newLocationParts) {
								return $this->getUrlContents($newLocation, ++$redirectFollowCount, $visitedLinks, $redirectCode, $originalUrl);
							}
						}
					}
					else if ($this->options['followRedirects'] && ($httpCode == 302 || $httpCode == 307)) { //--temporary redirect
						$this->writeLog('redirects', "Temporary Redirect", [$url, $newLocation]);
						if (!in_array($url, $visitedLinks)) {
							$visitedLinks[] = $url;
							if (!$redirectCode)
								$redirectCode = $httpCode;
							if (!$originalUrl)
								$originalUrl = $url;
							$newLocationParts = $this->parseUrl($newLocation);
							if ($newLocationParts && $newLocationParts['host'] == '')
								$newLocationParts = $this->mergeUrlParts($urlParts, $newLocationParts);
							if ($newLocationParts) {
								return $this->getUrlContents($newLocation, ++$redirectFollowCount, $visitedLinks, $redirectCode, $originalUrl);
							}
						}
					}
				}
				else if ($httpCode == 200) {
					if ($urlParts['path'] == '') {
						// echo "empty urlParts['path']\n";
						$urlParts['path'] = '/';
						$lastUrl = $this->buildUrl($urlParts);
					}
					if ($originalUrl) {
						$lastUrl = $originalUrl;
					}
					return array($lastUrl, $data);
				}
			}
			return array(null, null);
		}
	}

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

	public function urlDepth($url) {
		$urlParts = $this->parseUrl($url);
		if (sizeof($urlParts['pathParts']) > 0)
			return sizeof($urlParts['pathParts']);
		else
			return 1;
	}

	public function startsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) !== FALSE && strpos($haystack, $needle) === 0;
	}

	public function endsWith($haystack, $needle) {
		if ($haystack == '') return false;
		return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
	}
}