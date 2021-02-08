<?php
namespace LynkCMS\Component\Http\HtmlDocument\Partial;

use LynkCMS\Component\Http\HtmlDocument\AbstractDocument;

class Head extends AbstractPartial {
	public function __construct(array $config = []) {
		$this->data = $this->defaults();
	}
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
	public function printHead() {
		echo $this->get();
	}
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
	public function renderCharset($tab = "\t", $nl = "\n") {
		$charset = $this->getData('charset');
		if ($charset) {
			return "{$tab}<meta charset=\"{$charset}\">{$nl}";
		}
		return '';
	}
	public function renderViewport($tab = "\t", $nl = "\n") {
		$viewport = $this->getData('viewport');
		if ($viewport) {
			return "{$tab}<meta name=\"viewport\" content=\"{$viewport}\">{$nl}";
		}
		return '';
	}
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
	public function renderMeta($tab = "\t", $nl = "\n") {
		$meta = $this->getData('meta');
		return (
			(sizeof($meta) ? $tab : '') .
			implode($nl . $tab, $meta) .
			(sizeof($meta) ? $nl : '')
		);
	}
	public function renderDescription($tab = "\t", $nl = "\n") {
		$description = $this->getData('description');
		if ($description) {
			return "{$tab}<meta name=\"description\" content=\"{$description}\">{$nl}"; 
		}
		return '';
	}
	public function renderKeywords($tab = "\t", $nl = "\n") {
		$keywords = $this->getData('keywords');
		if ($keywords) {
			return "{$tab}<meta name=\"keywords\" content=\"{$keywords}\">{$nl}"; 
		}
		return '';
	}
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
	public function renderIcon($tab = "\t", $nl = "\n") {
		$icon = $this->getData('icon');
		if ($icon) {
			return "{$tab}<link href=\"{$icon}\" rel=\"shortcut icon\">{$nl}"; 
		}
		return '';
	}
	public function renderLinks($tab = "\t", $nl = "\n") {
		$links = $this->getData('links');
		return (
			(sizeof($links) ? $tab : '') .
			implode($nl . $tab, $links) .
			(sizeof($links) ? $nl : '')
		);
	}
	public function renderCss($tab = "\t", $nl = "\n") {
		$css = $this->getData('css');
		return (
			(sizeof($css) ? $tab : '') .
			implode($nl . $tab, $css) .
			(sizeof($css) ? $nl : '')
		);
	}
	public function renderCssCode($tab = "\t", $nl = "\n") {
		$cssCode = $this->getData('cssCode');
		$string = '';
		foreach ($cssCode as $css) {
			$string .= "{$tab}<style type=\"text/css\">{$css}</style>{$nl}";
		}
		return $string;
	}
	public function renderScripts($tab = "\t", $nl = "\n") {
		$scripts = $this->getData('scripts');
		return (
			(sizeof($scripts) ? $tab : '') .
			implode($nl . $tab, $scripts) .
			(sizeof($scripts) ? $nl : '')
		);
	}
	public function renderScriptCode($tab = "\t", $nl = "\n") {
		$scriptsCode = $this->getData('scriptCode');
		$string = '';
		foreach ($scriptsCode as $script) {
			$string .= "{$tab}<script>{$script}</script>{$nl}";
		}
		return $string;
	}
	public function renderExtra($tab = "\t", $nl = "\n") {
		$extras = $this->getData('extra');
		return (
			(sizeof($extras) ? $tab : '') .
			implode($nl . $tab, $extras) .
			(sizeof($extras) ? $nl : '')
		);
	}
	public function getAuthor() {
		return $this->getData('author');
	}
	public function setAuthor($author) {
		$this->setData('author', $author);
	}
	public function getCharset() {
		return $this->getData('charset');
	}
	public function setCharset($charset) {
		$this->setData('charset', $charset);
	}
	public function getViewport() {
		return $this->getData('viewport');
	}
	public function setViewport($viewport) {
		$this->setData('viewport', $viewport);
	}
	public function getRobots() {
		return $this->getData('robots');
	}
	public function setRobots($robots) {
		$this->setData('robots', $robots);
	}
	public function getIcon() {
		return $this->getData('icon');
	}
	public function setIcon($icon) {
		$this->setData('icon', $icon);
	}
	public function getMeta($key) {
		$meta = $this->getData('meta');
		if (array_key_exists($key, $meta)) {
			return $meta[$key];
		}
		else if ($metaKey = array_search($key, $meta) !== false) {
			return $meta[$metaKey];
		}
	}
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
	public function getLink($key) {
		$links = $this->getData('links');
		if (array_key_exists($key, $links)) {
			return $links[$key];
		}
		else if ($linksKey = array_search($key, $links) !== false) {
			return $links[$linksKey];
		}
	}
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
	public function getCss($key) {
		$css = $this->getData('css');
		if (array_key_exists($key, $css)) {
			return $css[$key];
		}
		else if ($cssKey = array_search($key, $css) !== false) {
			return $css[$cssKey];
		}
	}
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
	public function getCssCode($key) {
		$css = $this->getData('cssCode');
		if (array_key_exists($key, $css)) {
			return $css[$key];
		}
		else if ($cssKey = array_search($key, $css) !== false) {
			return $css[$cssKey];
		}
	}
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
	public function getTitle() {
		return $this->getData('title');
	}
	public function setTitle($title) {
		$this->setData('title', $title);
	}
	public function getTitleSeparator() {
		return $this->getData('titleSeparator');
	}
	public function setTitleSeparator($separator) {
		$this->setData('titleSeparator', $separator);
	}
	public function getTitleSuffix() {
		return $this->getData('titleSuffix');
	}
	public function setTitleSuffix($titleSuffix) {
		$this->setData('titleSuffix', $titleSuffix);
	}
	public function getTitlePrefix() {
		return $this->getData('titlePrefix');
	}
	public function setTitlePrefix($titlePrefix) {
		$this->setData('titlePrefix', $titlePrefix);
	}
	public function getDescription() {
		return $this->getData('description');
	}
	public function setDescription($description) {
		$this->setData('description', $description);
	}
	public function getKeywords() {
		return $this->getData('keywords');
	}
	public function setKeywords($keywords) {
		$this->setData('keywords', $keywords);
	}
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