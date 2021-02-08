<?php
namespace LynkCMS\Component\Http\HtmlDocument;

use LynkCMS\Component\Http\HtmlDocument\Partial;

class Document extends AbstractDocument {
	private $head;
	private $body;
	public function __construct(Array $config = []) {
		$this->data = $this->defaults();
		$this->head = new Partial\Head();
		$this->body = new Partial\Body();
		$this->load($config);
	}
	public function head() {
		return $this->head;
	}
	public function body() {
		return $this->body;
	}
	public function copy() {
		$doc = new Document();
		$doc->merge($this);
		return $doc;
	}
	public function load(Array $config) {
		parent::load($config);
		if (isset($config['minify']) && $config['minify'] != '') {
			$this->setMinify($config['minify']);
		}
		if (isset($config['ieSupport']) && $config['ieSupport'] != '') {
			$this->setIeSupport($config['ieSupport']);
		}
		if (isset($config['html4Support']) && $config['html4Support'] != '') {
			$this->setHtml4Support($config['html4Support']);
		}
		if (isset($config['html5Support']) && $config['html5Support'] != '') {
			$this->setHtml5Support($config['html5Support']);
		}
		if (isset($config['language']) && $config['language'] != '') {
			$this->setLanguage($config['language']);
		}
		if (isset($config['name']) && $config['name'] != '') {
			$this->setName($config['name']);
		}
		if (isset($config['head']) && sizeof($config['head'])) {
			$this->head()->load($config['head']);
		}
		if (isset($config['body']) && sizeof($config['body'])) {
			$this->body()->load($config['body']);
		}
	}
	public function merge(AbstractDocument $document) {
		parent::merge($document);
		$this->setIeSupport($document->getIeSupport());
		$this->setHtml4Support($document->getHtml4Support());
		$this->setHtml5Support($document->getHtml5Support());
		if ($document->getLanguage() && $document->getLanguage() != '') {
			$this->setLanguage($document->getLanguage());
		}
		if ($document->getName() && $document->getName()) {
			$this->setName($document->getName());
		}
		$this->head()->merge($document->head());
		$this->body()->merge($document->body());
	}
	public function reset($preserveDetails = false) {
		parent::reset($preserveDetails);
		if ($preserveDetails) {
			$name = $this->getName();
			$this->data = $this->defaults();
			$this->setName($name);
		}
		else {
			$this->data = $this->defaults();
		}
		$this->head()->reset($preserveDetails);
		$this->body()->reset();
	}
	public function getStart() {
		$html = $this->renderDoctype() .
				$this->head->get() .
				$this->body->getStart();
		return $html;
	}
	public function printStart() {
		echo $this->getStart();
	}
	public function get() {
		return $this->getStart() . $this->body->getContent() . $this->getEnd();
	}
	public function printDocument() {
		echo $this->get();
	}
	public function getEnd() {
		$html = $this->body->getEnd() .
				"</html>";
		return $html;
	}
	public function printEnd() {
		echo $this->getEnd();
	}
	public function renderDoctype() {
		$nl = $this->getData('minify') ? '' : "\n";
		$classes = $this->getClasses();
		$attributes = $this->getData('attributes');
		if ($this->getName()) {
			$classes .= ($classes != '' ? ' ' : '') . "page-{$this->data['name']}";
			if (!array_key_exists('id', $attributes)) {
				$attributes['id'] = "page-{$this->data['name']}";
			}
		}
		$lang = $this->getLanguage() ? " lang=\"{$this->getLanguage()}\"" : '';
		$classes = htmlentities($classes);
		$class = $classes != '' ? " class=\"{$classes}\"" : '';
		$doctype = '';
		unset($attributes['class']);
		$attributes = $this->renderAttributeList($attributes);
		if ($this->getIeSupport()) {
			$doctype = (
				("<!--[if IE 8 ]><html{$lang}" . ($classes != '' ? " class=\"ie8 {$classes}\"" : " class=\"ie8\"") . "{$attributes}><![endif]-->{$nl}") .
				("<!--[if IE 9 ]><html{$lang}" . ($classes != '' ? " class=\"ie9 {$classes}\"" : " class=\"ie8\"") . "{$attributes}><![endif]-->{$nl}") .
				("<!--[if (gt IE 9)|!(IE)]><!--><html{$lang}{$class}{$attributes}><!--<![endif]-->{$nl}")
			);
		}
		else {
			$doctype = "<html{$lang}{$class}{$attributes}>{$nl}";
		}
		if ($this->getHtml4Support()) {
			if ($this->getHtml4Support() == 'strict') {
				$doctype = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">{$nl}{$doctype}";
			}
			else if ($this->getHtml4Support() == 'traditional') {
				$doctype = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">{$nl}{$doctype}";
			}
			else if ($this->getHtml4Support() == 'frameset') {
				$doctype = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">{$nl}{$doctype}";
			}
		}
		else if ($this->getHtml5Support()) {
			$doctype = "<!DOCTYPE html>{$nl}{$doctype}";
		}
		return $doctype;
	}
	public function getIeSupport() {
		return $this->getData('ieSupport');
	}
	public function setIeSupport($ieSupport) {
		$this->setData('ieSupport', $ieSupport);
	}
	public function getHtml4Support() {
		return $this->getData('html4Support');
	}
	public function setHtml4Support($type) {
		if ($type) {
			$type = in_array($type, ['strict', 'traditional', 'frameset']) ? $type : 'strict';
		}
		else {
			$type = false;
		}
		$this->setData('html4Support', $type);
	}
	public function getHtml5Support() {
		return $this->getData('html5Support');
	}
	public function setHtml5Support($support) {
		$this->setData('html5Support', $support);
	}
	public function getLanguage() {
		return $this->getData('language');
	}
	public function setLanguage($language) {
		$this->setData('language', $language);
	}
	public function getName() {
		return $this->getData('name');
	}
	public function setName($name) {
		$this->setData('name', $name);
	}
	public function getMinify() {
		return $this->getData('minify');
	}
	public function setMinify($minify) {
		$this->setData('minify', $minify);
		$this->head->setData('minify', $minify);
		$this->body->setData('minify', $minify);
	}
	protected function defaults() {
		return array_merge(parent::defaults(), [
			'ieSupport' => false,
			'html4Support' => false,
			'html5Support' => true,
			'language' => 'en',
			'name' => null,
			'minify' => false
		]);
	}
}