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

namespace Lynk\Component\Http\HtmlDocument;

use Lynk\Component\Http\HtmlDocument\Partial;

/**
 * HTML document class.
 */
class Document extends AbstractDocument {

	/**
	 * @var Partial\Head Head partial class.
	 */
	private $head;

	/**
	 * @var Partial\Body Body partial class.
	 */
	private $body;

	/**
	 * @param Array $config Optional. Document configuration.
	 */
	public function __construct(Array $config = []) {
		$this->data = $this->defaults();
		$this->head = new Partial\Head();
		$this->body = new Partial\Body();
		$this->load($config);
	}

	/**
	 * Get head partial.
	 * 
	 * @return Partial\Head Head partial class.
	 */
	public function head() {
		return $this->head;
	}

	/**
	 * Get body partial.
	 * 
	 * @return Partial\Body Body partial class.
	 */
	public function body() {
		return $this->body;
	}

	/**
	 * Copy current document instance.
	 * 
	 * @return Document Copy of current instance.
	 */
	public function copy() {
		$doc = new Document();
		$doc->merge($this);
		return $doc;
	}

	/**
	 * Load a document configuration.
	 * 
	 * @param Array $config The configuration.
	 */
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

	/**
	 * Merge an existing document instance.
	 * 
	 * @param AbstractDocument $document The document instance.
	 */
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

	/**
	 * Reset document data.
	 * 
	 * @param bool $preserveDetails Optional. Preserve details.
	 */
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

	/**
	 * Get the start of the document output.
	 * 
	 * @return string The start of the document.
	 */
	public function getStart() {
		$html = $this->renderDoctype() .
				$this->head->get() .
				$this->body->getStart();
		return $html;
	}

	/**
	 * Print the document start.
	 */
	public function printStart() {
		echo $this->getStart();
	}

	/**
	 * Get the document output.
	 * 
	 * @return string The rendered document.
	 */
	public function get() {
		return $this->getStart() . $this->body->getContent() . $this->getEnd();
	}

	/**
	 * Print the document.
	 */
	public function printDocument() {
		echo $this->get();
	}

	/**
	 * Get the end of the document.
	 * 
	 * @return string The end of the document.
	 */
	public function getEnd() {
		$html = $this->body->getEnd() .
				"</html>";
		return $html;
	}

	/**
	 * Print the end of the document.
	 */
	public function printEnd() {
		echo $this->getEnd();
	}

	/**
	 * Render and return the DOCTYPE of the document.
	 * 
	 * @return string The DOCTYPE.
	 */
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

	/**
	 * Get IE support.
	 * 
	 * @return bool True if IE is supported, fasle if otherwise.
	 */
	public function getIeSupport() {
		return $this->getData('ieSupport');
	}

	/**
	 * Set IE support.
	 * 
	 * @param bool $ieSupport Whether or not old IE versions are supported.
	 */
	public function setIeSupport($ieSupport) {
		$this->setData('ieSupport', $ieSupport);
	}

	/**
	 * Get HTML 4 support. Either 'strict', 'traditional' or 'frameset'.
	 * Defaults to 'strict' if value provided is neither of the 3 options.
	 * False by default.
	 * 
	 * @return mixed HTML support.
	 */
	public function getHtml4Support() {
		return $this->getData('html4Support');
	}

	/**
	 * Set HTML 4 support.
	 * Defaults to 'strict' if value provided is neither of the 3 options.
	 * False by default.
	 */
	public function setHtml4Support($type) {
		if ($type) {
			$type = in_array($type, ['strict', 'traditional', 'frameset']) ? $type : 'strict';
		}
		else {
			$type = false;
		}
		$this->setData('html4Support', $type);
	}

	/**
	 * Get HTML 5 support.
	 * 
	 * @return bool True if HTML 5 is supported, false otherwise.
	 */
	public function getHtml5Support() {
		return $this->getData('html5Support');
	}

	/**
	 * Set HTML 5 support.
	 * 
	 * @param bool $support HTML 5 support.
	 */
	public function setHtml5Support($support) {
		$this->setData('html5Support', $support);
	}

	/**
	 * Get language.
	 * 
	 * @return string Language setting.
	 */
	public function getLanguage() {
		return $this->getData('language');
	}

	/**
	 * Set language.
	 * 
	 * @param string $language Document language.
	 */
	public function setLanguage($language) {
		$this->setData('language', $language);
	}

	/**
	 * Get page/document name.
	 * 
	 * @return string Page name.
	 */
	public function getName() {
		return $this->getData('name');
	}

	/**
	 * Set page/document name.
	 * 
	 * @param string $name Page name.
	 */
	public function setName($name) {
		$this->setData('name', $name);
	}

	/**
	 * Get minify setting.
	 * 
	 * @return bool Whether or not to minify rendered document.
	 */
	public function getMinify() {
		return $this->getData('minify');
	}

	/**
	 * Set minify setting.
	 * 
	 * @param bool $minify Whether or not to minify rendered document.
	 */
	public function setMinify($minify) {
		$this->setData('minify', $minify);
		$this->head->setData('minify', $minify);
		$this->body->setData('minify', $minify);
	}

	/**
	 * Get default config values.
	 * 
	 * @return Array Default values.
	 */
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