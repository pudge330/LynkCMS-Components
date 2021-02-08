<?php
namespace BGStudios\Component\Http\HtmlDocument;

class AbstractDocument {
	protected $data = array();
	public function load(Array $config) {
		if (isset($config['attributes']) && sizeof($config['attributes'])) {
			$this->setData('attributes', array_merge(
				$this->getData('attributes'), $config['attributes']
			));
		}
	}
	public function merge(AbstractDocument $document) {
		$this->setData('attributes', array_merge(
			$this->getData('attributes'), $document->getData('attributes')
		));
	}
	public function reset($preserveDetails = false) {
		$this->setData('attributes', []);
	}
	public function getData($key) {
		if ($this->hasData($key)) {
			return $this->data[$key];
		}
	}
	public function setData($key, $value) {
		$this->data[$key] = $value;
	}
	public function hasData($key) {
		return array_key_exists($key, $this->data);
	}
	public function removeData($key) {
		unset($this->data[$key]);
	}
	public function getAttribute($key) {
		if ($this->hasAttribute($key)) {
			return $this->data['attributes'][$key];
		}
	}
	public function setAttribute($key, $value) {
		$this->data['attributes'][$key] = $value;
	}
	public function hasAttribute($key) {
		return array_key_exists($key, $this->data['attributes']);
	}
	public function removeAttribute($key) {
		if ($this->hasAttribute($key)) {
			unset($this->data['attributes'][$key]);
		}
	}
	public function getClasses() {
		if ($this->hasAttribute('class')) {
			return $this->data['attributes']['class'];
		}
		else
			return '';
	}
	public function setClasses($classes) {
		$this->data['attributes']['class'] = $classes;
	}
	public function addClass($class) {
		if (!$this->hasAttribute('class')) {
			$this->setAttribute('class', '');
		}
		$this->data['attributes']['class'] .= (strlen($this->data['attributes']['class']) ? ' ' : '') . $class;
	}
	public function hasClass($class) {
		return $this->hasAttribute('class') && strpos(" {$this->getAttribute('class')} ", $class) !== false;
	}
	public function removeClass($class) {
		if ($this->hasAttribute('class')) {
			$classes = " {$this->getClasses()} ";
			$classes = str_replace(" {$class} ", ' ', $classes);
			$this->setClasses($classes);
		}
	}
	public function renderAttributeList($attributes) {
		$string = '';
		foreach ($attributes as $key => $value) {
			$string .= ' ' . ($value !== null
				? "{$key}=\"" . htmlentities($value) . '"'
				: "{$key}");
		}
		return $string;
	}
	protected function defaults() {
		return [
			'attributes' => []
		];
	}
}