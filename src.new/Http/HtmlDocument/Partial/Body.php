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
 * @subpackage Http
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Http\HtmlDocument\Partial;

use LynkCMS\Component\Http\HtmlDocument\AbstractDocument;

/**
 * Body partial class.
 */
class Body extends AbstractPartial {

	/**
	 * @param Array $config Optional. Body configuration.
	 */
	public function __construct(array $config = []) {
		$this->data = $this->defaults();
	}

	/**
	 * Load configuration.
	 * 
	 * @param Array $config Body configuration.
	 */
	public function load(Array $config) {
		parent::load($config);
	}

	/**
	 * Merge body partial instance.
	 * 
	 * @param AbstractDocument $document Body partial instance.
	 */
	public function merge(AbstractDocument $document) {
		parent::merge($document);
	}

	/**
	 * Reset partial.
	 * 
	 * @param bool $preserveDetails Optional. Preserve details.
	 */
	public function reset($preserveDetails = false) {
		parent::reset($preserveDetails);
	}

	/**
	 * Get start of body.
	 * 
	 * @return string Start pf body output.
	 */
	public function getStart() {
		$attributes = $this->renderAttributeList($this->getData('attributes'));
		return "<body{$attributes}>" . (!$this->getData('minify') && $this->getContent() ? "\n" : '');
	}

	/**
	 * Print start of body output.
	 */
	public function printStart() {
		echo $this->getStart();
	}

	/**
	 * Get body output.
	 * 
	 * @return string Rendered body output.
	 */
	public function get() {
		return $this->getStart() . $this->getContent() . $this->getEnd();
	}

	/**
	 * Print rendered body.
	 */
	public function printBody() {
		echo $this->get();
	}

	/**
	 * Get end of body output.
	 * 
	 * @return string End of rendered body output.
	 */
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

	/**
	 * Print end of body content.
	 */
	public function printEnd() {
		echo $this->getEnd();
	}

	/**
	 * Render scripts.
	 * 
	 * @param string $tab Optional. Tab indentation.
	 * @param string $nl Optional. Newline, linebreak.
	 * 
	 * @return string Rendered script code.
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
	 * Render script code.
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
	 * Render body extra.
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
	 * Get body content.
	 * 
	 * @return string Body content.
	 */
	public function getContent() {
		return $this->getData('content');
	}

	/**
	 * Set body content.
	 * 
	 * @param string $content Body content.
	 */
	public function setContent($content) {
		$this->setData('content', $content);
	}

	/**
	 * Default configuration values.
	 * 
	 * @return Array Configuration values.
	 */
	protected function defaults() {
		return array_merge(parent::defaults(), [
			'content' => null,
			'minify' => false
		]);
	}
}