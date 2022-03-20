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
 * @subpackage Http
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Http\HtmlDocument\Partial;

use Lynk\Component\Http\HtmlDocument\AbstractDocument;

/**
 * Head partial class.
 */
class Head extends AbstractPartial {

	/**
	 * @param Array $config Optional. Head configuration.
	 */
	public function __construct(array $config = []) {
		$this->data = $this->defaults();
	}

	/**
	 * Load configuration.
	 * 
	 * @param Array $config Head configuration.
	 */
	public function load(Array $config) {
		parent::load($config);
		if (isset($config['author']) && $config['author'] != '') {
			$this->setAuthor($config['author']);
		}
		if (isset($config['charset']) && $config['charset'] != '') {
			$this->setCharset($config['charset']);
		}
		if (isset($config['viewport']) && $config['viewport'] != '') {
			$this->setViewport($config['viewport']);
		}
		if (isset($config['robots']) && $config['robots'] != '') {
			$this->setRobots($config['robots']);
		}
		if (isset($config['description']) && $config['description'] != '') {
			$this->setDescription($config['description']);
		}
		if (isset($config['keywords']) && $config['keywords'] != '') {
			$this->setKeywords($config['keywords']);
		}
		if (isset($config['title']) && $config['title'] != '') {
			$this->setTitle($config['title']);
		}
		if (isset($config['titlePrefix']) && $config['titlePrefix'] != '') {
			$this->setTitlePrefix($config['titlePrefix']);
		}
		if (isset($config['titleSuffix']) && $config['titleSuffix'] != '') {
			$this->setTitleSuffix($config['titleSuffix']);
		}
		if (isset($config['titleSeparator']) && $config['titleSeparator'] != '') {
			$this->setTitleSeparator($config['titleSeparator']);
		}
		if (isset($config['icon']) && $config['icon'] != '') {
			$this->setIcon($config['icon']);
		}
		if (isset($config['meta']) && sizeof($config['meta'])) {
			foreach ($config['meta'] as $key => $meta) {
				if (is_numeric($key) && !$this->hasMeta($meta)) {
					$this->setMeta($meta);
				}
				else {
					$this->setMeta($key, $meta);
				}
			}
		}
		if (isset($config['links']) && sizeof($config['links'])) {
			foreach ($config['links'] as $key => $link) {
				if (is_numeric($key) && !$this->hasLink($link)) {
					$this->setLink($link);
				}
				else {
					$this->setLink($key, $link);
				}
			}
		}
		if (isset($config['css']) && sizeof($config['css'])) {
			foreach ($config['css'] as $key => $css) {
				if (preg_match('/^\[file\]/', $css)) {
					$css = preg_replace('/^\[file\]/', '', $css);
					$css = "<link href=\"{$css}\" type=\"text/css\" rel=\"stylesheet\">";
				}
				else if (preg_match('/^\[code\]/', $css)) {
					$css = preg_replace('/^\[code\]/', '', $css);
					$css = "<style type=\"text/css\">{$css}</style>";
				}
				if (is_numeric($key) && !$this->hasCss($css)) {
					$this->setCss($css);
				}
				else {
					$this->setCss($key, $css);
				}
			}
		}
		if (isset($config['cssCode']) && sizeof($config['cssCode'])) {
			foreach ($config['cssCode'] as $key => $css) {
				if (is_numeric($key) && !$this->hasCss($css)) {
					$this->setCssCode($css);
				}
				else {
					$this->setCssCode($key, $css);
				}
			}
		}
	}

	/**
	 * Merge head partial instance.
	 * 
	 * @param AbstractDocument $document Head partial instance.
	 */
	public function merge(AbstractDocument $document) {
		parent::merge($document);
		if ($document->getAuthor() && $document->getAuthor() != '') {
			$this->setAuthor($document->getAuthor());
		}
		if ($document->getCharset() && $document->getCharset() != '') {
			$this->setCharset($document->getCharset());
		}
		if ($document->getViewport() && $document->getViewport() != '') {
			$this->setViewport($document->getViewport());
		}
		if ($document->getRobots() && $document->getRobots() != '') {
			$this->setRobots($document->getRobots());
		}
		if ($document->getDescription() && $document->getDescription() != '') {
			$this->setDescription($document->getDescription());
		}
		if ($document->getKeywords() && $document->getKeywords() != '') {
			$this->setKeywords($document->getKeywords());
		}
		if ($document->getTitle() && $document->getTitle() != '') {
			$this->setTitle($document->getTitle());
		}
		if ($document->getTitlePrefix() && $document->getTitlePrefix() != '') {
			$this->setTitlePrefix($document->getTitlePrefix());
		}
		if ($document->getTitleSuffix() && $document->getTitleSuffix() != '') {
			$this->setTitleSuffix($document->getTitleSuffix());
		}
		if ($document->getTitleSeparator() && $document->getTitleSeparator() != '') {
			$this->setTitleSeparator($document->getTitleSeparator());
		}
		if ($document->getIcon() && $document->getIcon() != '') {
			$this->setIcon($document->getIcon());
		}
		$meta = $document->getData('meta');
		foreach ($meta as $key => $m) {
			if (is_numeric($key)) {
				if (!$this->hasMeta($m)) {
					$this->setMeta($m);
				}
			}
			else {
				$this->setMeta($key, $m);
			}
		}
		$links = $document->getData('links');
		foreach ($links as $key => $l) {
			if (is_numeric($key)) {
				if (!$this->hasLink($l)) {
					$this->setLink($l);
				}
			}
			else {
				$this->setLink($key, $l);
			}
		}
		$css = $document->getData('css');
		foreach ($css as $key => $c) {
			if (is_numeric($key)) {
				if (!$this->hasCss($c)) {
					$this->setCss($c);
				}
			}
			else {
				$this->setCss($key, $c);
			}
		}
		$cssCode = $document->getData('cssCode');
		foreach ($cssCode as $key => $script) {
			if (is_numeric($key)) {
				if (!$this->hasCssCode($script)) {
					$this->setCssCode($script);
				}
			}
			else {
				$this->setCssCode($key, $script);
			}
		}
	}

	/**
	 * Reset partial.
	 * 
	 * @param bool $preserveDetails Optional. Preserve details.
	 */
	public function reset($preserveDetails = false) {
		parent::reset($preserveDetails);
		if ($preserveDetails) {
			$title = $this->getTitle();
			$titlePrefix = $this->getTitlePrefix();
			$titleSuffix = $this->getTitleSuffix();
			$titleSeparator = $this->getTitleSeparator();
			$description = $this->getDescription();
			$keywords = $this->getKeywords();
			$this->data = $this->defaults();
			$this->setTitle($title);
			$this->setTitlePrefix($titlePrefix);
			$this->setTitleSuffix($titleSuffix);
			$this->setTitleSeparator($titleSeparator);
			$this->setDescription($description);
			$this->setKeywords($keywords);
		}
		else {
			$this->data = $this->defaults();
		}
	}

	/**
	 * Get head output.
	 * 
	 * @return string Rendered head output.
	 */
	public function get() {
		$nl = $this->getData('minify') ? '' : "\n";
		$tab = $this->getData('minify') ? '' : "\t";
		$attributes = $this->renderAttributeList($this->getData('attributes'));
		// $tab = '';
		$html = "" .
				"<head{$attributes}>{$nl}" .
				$this->renderAuthor($tab, $nl) .
				$this->renderCharset($tab, $nl) .
				$this->renderViewport($tab, $nl) .
				$this->renderRobots($tab, $nl) .
				$this->renderMeta($tab, $nl) .
				$this->renderDescription($tab, $nl) .
				$this->renderKeywords($tab, $nl) .
				$this->renderTitle($tab, $nl) .
				$this->renderIcon($tab, $nl) .
				$this->renderLinks($tab, $nl) .
				$this->renderCss($tab, $nl) .
				$this->renderCssCode($tab, $nl) .
				$this->renderScripts($tab, $nl) .
				$this->renderScriptCode($tab, $nl) .
				$this->renderExtra($tab, $nl) .
				"</head>{$nl}";
		return $html;
	}

	/**
	 * Print rendered head.
	 */
	public function printHead() {
		echo $this->get();
	}

	/**
	 * Get rendered author tag.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered author tag.
	 */
	public function renderAuthor($tab = "\t", $nl = "\n") {
		$author = $this->getData('author');
		if ($author) {
			if (preg_match('/^\[file\]/', $author)) {
				$author = preg_replace('/^\[file\]/', '', $author);
				return "{$tab}<link href=\"{$author}\" type=\"text/plain\" rel=\"author\">{$nl}";
			}
			else {
				return "{$tab}<meta name=\"author\" content=\"{$author}\">{$nl}";
			}
		}
		return '';
	}

	/**
	 * Get rendered charset tag.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered charset tag.
	 */
	public function renderCharset($tab = "\t", $nl = "\n") {
		$charset = $this->getData('charset');
		if ($charset) {
			return "{$tab}<meta charset=\"{$charset}\">{$nl}";
		}
		return '';
	}

	/**
	 * Get rendered viewport tag.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered viewport tag.
	 */
	public function renderViewport($tab = "\t", $nl = "\n") {
		$viewport = $this->getData('viewport');
		if ($viewport) {
			return "{$tab}<meta name=\"viewport\" content=\"{$viewport}\">{$nl}";
		}
		return '';
	}

	/**
	 * Get rendered robot tags.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered robot tags.
	 */
	public function renderRobots($tab = "\t", $nl = "\n") {
		$robots = explode(',', $this->getData('robots'));
		$string = '';
		foreach ($robots as $robot) {
			if ($robot != '') {
				$string .= "{$tab}<meta name=\"robots\" content=\"{$robot}\">{$nl}";
			}
		}
		return $string;
	}

	/**
	 * Get rendered meta tags.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered meta tags.
	 */
	public function renderMeta($tab = "\t", $nl = "\n") {
		$meta = $this->getData('meta');
		return (
			(sizeof($meta) ? $tab : '') .
			implode($nl . $tab, $meta) .
			(sizeof($meta) ? $nl : '')
		);
	}

	/**
	 * Get rendered description tag.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered description tag.
	 */
	public function renderDescription($tab = "\t", $nl = "\n") {
		$description = $this->getData('description');
		if ($description) {
			return "{$tab}<meta name=\"description\" content=\"{$description}\">{$nl}"; 
		}
		return '';
	}

	/**
	 * Get rendered keywords tag.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered keywords tag.
	 */
	public function renderKeywords($tab = "\t", $nl = "\n") {
		$keywords = $this->getData('keywords');
		if ($keywords) {
			return "{$tab}<meta name=\"keywords\" content=\"{$keywords}\">{$nl}"; 
		}
		return '';
	}

	/**
	 * Get rendered title tag.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered title tag.
	 */
	public function renderTitle($tab = "\t", $nl = "\n") {
		$separator = $this->getTitleSeparator();
		$separator = $separator && $separator != ''
			? $separator
			: ' ';
		$title = $this->getTitlePrefix();
		$title .= ($title != '' ? $separator : '') . $this->getTitle();
		if ($this->getTitleSuffix() && $this->getTitleSuffix() != '') {
			$title .= ($title != '' ? $separator : '') . $this->getTitleSuffix();
		}
		return $title != ''
			? "{$tab}<title>{$title}</title>{$nl}"
			: '';
	}

	/**
	 * Get rendered icon tag.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered icon tag.
	 */
	public function renderIcon($tab = "\t", $nl = "\n") {
		$icon = $this->getData('icon');
		if ($icon) {
			return "{$tab}<link href=\"{$icon}\" rel=\"shortcut icon\">{$nl}"; 
		}
		return '';
	}

	/**
	 * Get rendered link tags.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered link tags.
	 */
	public function renderLinks($tab = "\t", $nl = "\n") {
		$links = $this->getData('links');
		return (
			(sizeof($links) ? $tab : '') .
			implode($nl . $tab, $links) .
			(sizeof($links) ? $nl : '')
		);
	}

	/**
	 * Get rendered css.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered css.
	 */
	public function renderCss($tab = "\t", $nl = "\n") {
		$css = $this->getData('css');
		return (
			(sizeof($css) ? $tab : '') .
			implode($nl . $tab, $css) .
			(sizeof($css) ? $nl : '')
		);
	}

	/**
	 * Get rendered css code.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered css code.
	 */
	public function renderCssCode($tab = "\t", $nl = "\n") {
		$cssCode = $this->getData('cssCode');
		$string = '';
		foreach ($cssCode as $css) {
			$string .= "{$tab}<style type=\"text/css\">{$css}</style>{$nl}";
		}
		return $string;
	}

	/**
	 * Get rendered scripts.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered scripts.
	 */
	public function renderScripts($tab = "\t", $nl = "\n") {
		$scripts = $this->getData('scripts');
		return (
			(sizeof($scripts) ? $tab : '') .
			implode($nl . $tab, $scripts) .
			(sizeof($scripts) ? $nl : '')
		);
	}

	/**
	 * Get rendered script code.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered script code.
	 */
	public function renderScriptCode($tab = "\t", $nl = "\n") {
		$scriptsCode = $this->getData('scriptCode');
		$string = '';
		foreach ($scriptsCode as $script) {
			$string .= "{$tab}<script>{$script}</script>{$nl}";
		}
		return $string;
	}

	/**
	 * Get rendered extra code.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered extra code.
	 */
	public function renderExtra($tab = "\t", $nl = "\n") {
		$extras = $this->getData('extra');
		return (
			(sizeof($extras) ? $tab : '') .
			implode($nl . $tab, $extras) .
			(sizeof($extras) ? $nl : '')
		);
	}

	/**
	 * Get document author.
	 * 
	 * @return string Author.
	 */
	public function getAuthor() {
		return $this->getData('author');
	}

	/**
	 * Set document author.
	 * 
	 * @param string $author Author.
	 */
	public function setAuthor($author) {
		$this->setData('author', $author);
	}

	/**
	 * Get document charset.
	 * 
	 * @return string Charset.
	 */
	public function getCharset() {
		return $this->getData('charset');
	}

	/**
	 * Set document charset.
	 * 
	 * @param string $charset Charset.
	 */
	public function setCharset($charset) {
		$this->setData('charset', $charset);
	}

	/**
	 * Get browser viewport.
	 * 
	 * @return string Browser viewport.
	 */
	public function getViewport() {
		return $this->getData('viewport');
	}

	/**
	 * Set browser viewport.
	 * 
	 * @param string $viewport Browser viewport.
	 */
	public function setViewport($viewport) {
		$this->setData('viewport', $viewport);
	}

	/**
	 * Get robot settings.
	 * 
	 * @return string Robot settings.
	 */
	public function getRobots() {
		return $this->getData('robots');
	}

	/**
	 * Set robot settings.
	 * 
	 * @param string Robot settings, comma delimited.
	 */
	public function setRobots($robots) {
		$this->setData('robots', $robots);
	}

	/**
	 * Get favicon.
	 * 
	 * @return string Favicon.
	 */
	public function getIcon() {
		return $this->getData('icon');
	}

	/**
	 * Set favicon.
	 * 
	 * @param string Favicon path.
	 */
	public function setIcon($icon) {
		$this->setData('icon', $icon);
	}

	/**
	 * Get meta tag.
	 * 
	 * @param string $key Meta tag key or meta tag.
	 */
	public function getMeta($key) {
		$meta = $this->getData('meta');
		if (array_key_exists($key, $meta)) {
			return $meta[$key];
		}
		else if ($metaKey = array_search($key, $meta) !== false) {
			return $meta[$metaKey];
		}
	}

	/**
	 * Set meta tag.
	 * 
	 * @param mixed $var1 Either array of meta tags with optional name keys, a meta tag, or key of a meta tag.
	 * @param string $var2 Optional. Meta tag.
	 */
	public function setMeta($var1, $var2 = null) {
		if (is_array($var1)) {
			foreach ($var1 as $key => $val) {
				if (!is_numeric($key)) {
					$this->setMeta($val);
				}
				else {
					$this->setMeta($key, $val);
				}
			}
		}
		else {
			if ($var2) {
				$this->data['meta'][$var1] = $var2;
			}
			else {
				$this->data['meta'][] = $var1;
			}
		}
	}

	/**
	 * Check if meta tag exists, either by name or by meta tag.
	 * 
	 * @param string $key Meta tag key or meta tag.
	 * 
	 * @return bool True if meta tag exists, false otherwise.
	 */
	public function hasMeta($key) {
		$meta = $this->getData('meta');
		if (array_key_exists($key, $meta)) {
			return true;
		}
		else if (array_search($key, $meta) !== false) {
			return true;
		}
		return false;
	}

	/**
	 * Remove meta tag.
	 * 
	 * @param string $key Meta tag key or meta tag.
	 */
	public function removeMeta($key) {
		$meta = $this->getData('meta');
		if (array_key_exists($key, $meta)) {
			unset($meta[$key]);
		}
		else if ($metaKey = array_search($key, $meta) !== false) {
			unset($meta[$metaKey]);
		}
		$this->setData('meta', $meta);
	}

	/**
	 * Get link.
	 * 
	 * @param string $key Link key or link.
	 * 
	 * @return string Link file.
	 */
	public function getLink($key) {
		$links = $this->getData('links');
		if (array_key_exists($key, $links)) {
			return $links[$key];
		}
		else if ($linksKey = array_search($key, $links) !== false) {
			return $links[$linksKey];
		}
	}

	/**
	 * Set link.
	 * 
	 * @param mixed $var1 Either array of links with optional name keys, a link, or key of a link.
	 * @param string $var2 Optional. Link tag.
	 */
	public function setLink($var1, $var2 = null) {
		if (is_array($var1)) {
			foreach ($var1 as $key => $val) {
				if (!is_numeric($key)) {
					$this->setLink($val);
				}
				else {
					$this->setLink($key, $val);
				}
			}
		}
		else {
			if ($var2) {
				$this->data['links'][$var1] = $var2;
			}
			else {
				$this->data['links'][] = $var1;
			}
		}
	}

	/**
	 * Check if link exists, either by name or by link.
	 * 
	 * @param string $key Link key or link.
	 * 
	 * @return bool True if link exists, false otherwise.
	 */
	public function hasLink($key) {
		$links = $this->getData('links');
		if (array_key_exists($key, $links)) {
			return true;
		}
		else if (array_search($key, $links) !== false) {
			return true;
		}
		return false;
	}

	/**
	 * Remove link.
	 * 
	 * @param string $key Link key or link.
	 */
	public function removeLink($key) {
		$links = $this->getData('links');
		if (array_key_exists($key, $links)) {
			unset($links[$key]);
		}
		else if ($linksKey = array_search($key, $links) !== false) {
			unset($links[$linksKey]);
		}
		$this->setData('links', $links);
	}

	/**
	 * Get css.
	 * 
	 * @param string $key Css key or css.
	 * 
	 * @return string Css tag.
	 */
	public function getCss($key) {
		$css = $this->getData('css');
		if (array_key_exists($key, $css)) {
			return $css[$key];
		}
		else if ($cssKey = array_search($key, $css) !== false) {
			return $css[$cssKey];
		}
	}

	/**
	 * Set css.
	 * 
	 * @param mixed $var1 Either array of css tags with optional name keys, a css tag, or key of a css tag.
	 * @param string $var2 Optional. Css tag.
	 */
	public function setCss($var1, $var2 = null) {
		if (is_array($var1)) {
			foreach ($var1 as $key => $val) {
				if (!is_numeric($key)) {
					$this->setCss($val);
				}
				else {
					$this->setCss($key, $val);
				}
			}
		}
		else {
			if ($var2) {
				$this->data['css'][$var1] = $var2;
			}
			else {
				$this->data['css'][] = $var1;
			}
		}
	}

	/**
	 * Check if css tag exists, either by name or by tag.
	 * 
	 * @param string $key Css key or tag.
	 * 
	 * @return bool True if css exists, false otherwise.
	 */
	public function hasCss($key) {
		$css = $this->getData('css');
		if (array_key_exists($key, $css)) {
			return true;
		}
		else if (array_search($key, $css) !== false) {
			return true;
		}
		return false;
	}

	/**
	 * Remove css.
	 * 
	 * @param string $key Css key or css tag.
	 */
	public function removeCss($key) {
		$css = $this->getData('css');
		if (array_key_exists($key, $css)) {
			unset($css[$key]);
		}
		else if ($cssKey = array_search($key, $css) !== false) {
			unset($css[$cssKey]);
		}
		$this->setData('css', $css);
	}

	/**
	 * Get css code.
	 * 
	 * @param string $key Css code or css.
	 * 
	 * @return string Css code.
	 */
	public function getCssCode($key) {
		$css = $this->getData('cssCode');
		if (array_key_exists($key, $css)) {
			return $css[$key];
		}
		else if ($cssKey = array_search($key, $css) !== false) {
			return $css[$cssKey];
		}
	}

	/**
	 * Set css code.
	 * 
	 * @param mixed $var1 Either array of css code with optional name keys, a css code, or key of a css code.
	 * @param string $var2 Optional. Css code.
	 */
	public function setCssCode($var1, $var2 = null) {
		if (is_array($var1)) {
			foreach ($var1 as $key => $val) {
				if (!is_numeric($key)) {
					$this->setCssCode($val);
				}
				else {
					$this->setCssCode($key, $val);
				}
			}
		}
		else {
			if ($var2) {
				$this->data['cssCode'][$var1] = $var2;
			}
			else {
				$this->data['cssCode'][] = $var1;
			}
		}
	}

	/**
	 * Check if css code exists, either by name or by code.
	 * 
	 * @param string $key Css key or code.
	 * 
	 * @return bool True if css code exists, false otherwise.
	 */
	public function hasCssCode($key) {
		$css = $this->getData('cssCode');
		if (array_key_exists($key, $css)) {
			return true;
		}
		else if (array_search($key, $css) !== false) {
			return true;
		}
		return false;
	}

	/**
	 * Remove css code.
	 * 
	 * @param string $key Css key or css code.
	 */
	public function removeCssCode($key) {
		$css = $this->getData('cssCode');
		if (array_key_exists($key, $css)) {
			unset($css[$key]);
		}
		else if ($cssKey = array_search($key, $css) !== false) {
			unset($css[$cssKey]);
		}
		$this->setData('cssCode', $css);
	}

	/**
	 * Get page title.
	 * 
	 * @return string Page title.
	 */
	public function getTitle() {
		return $this->getData('title');
	}

	/**
	 * Set page title.
	 * 
	 * @param string $title Page title.
	 */
	public function setTitle($title) {
		$this->setData('title', $title);
	}

	/**
	 * Get title separator.
	 * 
	 * @return string Title separator.
	 */
	public function getTitleSeparator() {
		return $this->getData('titleSeparator');
	}

	/**
	 * Set title separator.
	 * 
	 * @param string $separator Title separator.
	 */
	public function setTitleSeparator($separator) {
		$this->setData('titleSeparator', $separator);
	}

	/**
	 * Get title suffix.
	 * 
	 * @return string Title suffix.
	 */
	public function getTitleSuffix() {
		return $this->getData('titleSuffix');
	}

	/**
	 * Set title suffix.
	 * 
	 * @param string $titleSuffix Title suffix.
	 */
	public function setTitleSuffix($titleSuffix) {
		$this->setData('titleSuffix', $titleSuffix);
	}

	/**
	 * Get title prefix.
	 * 
	 * @return string Title prefix.
	 */
	public function getTitlePrefix() {
		return $this->getData('titlePrefix');
	}

	/**
	 * Get title prefix.
	 * 
	 * @param string $titlePrefix Title prefix.
	 */
	public function setTitlePrefix($titlePrefix) {
		$this->setData('titlePrefix', $titlePrefix);
	}

	/**
	 * Get description.
	 * 
	 * @return string Page description.
	 */
	public function getDescription() {
		return $this->getData('description');
	}

	/**
	 * Set description.
	 * 
	 * @param string $description Page description.
	 */
	public function setDescription($description) {
		$this->setData('description', $description);
	}

	/**
	 * Get keywords.
	 * 
	 * @return string Keyword list.
	 */
	public function getKeywords() {
		return $this->getData('keywords');
	}

	/**
	 * Set keyword list.
	 * 
	 * @param string $keywords Keyword list.
	 */
	public function setKeywords($keywords) {
		$this->setData('keywords', $keywords);
	}

	/**
	 * Default configuration values.
	 * 
	 * @return Array Configuration values.
	 */
	protected function defaults() {
		return array_merge(parent::defaults(), [
			'author' => null,
			'charset' => 'utf8',
			'viewport' => null,
			'robots' => null,
			'icon' => null,
            'meta' => [],
            'links' => [],
            'css' => [],
            'cssCode' => [],
            'title' => null,
            'titleSeparator' => '-',
            'titlePrefix' => null,
            'titleSuffix' => null,
            'description' => null,
            'keywords' => null,
            'minify' => false
		]);
	}
}