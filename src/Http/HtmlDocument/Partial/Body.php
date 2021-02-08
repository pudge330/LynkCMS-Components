<?php
namespace BGStudios\Component\Http\HtmlDocument\Partial;

use BGStudios\Component\Http\HtmlDocument\AbstractDocument;

class Body extends AbstractPartial {
	public function __construct(array $config = []) {
		$this->data = $this->defaults();
	}
	public function load(Array $config) {
		parent::load($config);
	}
	public function merge(AbstractDocument $document) {
		parent::merge($document);
	}
	public function reset($preserveDetails = false) {
		parent::reset($preserveDetails);
	}
	public function getStart() {
		$attributes = $this->renderAttributeList($this->getData('attributes'));
		return "<body{$attributes}>" . (!$this->getData('minify') && $this->getContent() ? "\n" : '');
	}
	public function printStart() {
		echo $this->getStart();
	}
	public function get() {
		return $this->getStart() . $this->getContent() . $this->getEnd();
	}
	public function printBody() {
		echo $this->get();
	}
	public function getEnd() {
		$nl = $this->getData('minify') ? '' : "\n";
		$tab = $this->getData('minify') ? '' : "\t";
		// $tab = '';
		$html = "{$nl}" .
				$this->renderScripts($tab, $nl) .
				$this->renderScriptCode($tab, $nl) .
				$this->renderExtra($tab, $nl) .
				"</body>{$nl}";
		return $html;
	}
	public function printEnd() {
		echo $this->getEnd();
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
	public function getContent() {
		return $this->getData('content');
	}
	public function setContent($content) {
		$this->setData('content', $content);
	}
	protected function defaults() {
		return array_merge(parent::defaults(), [
			'content' => null,
			'minify' => false
		]);
	}
}