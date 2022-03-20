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

/**
 * Abstract document class.
 */
class AbstractDocument {

	/**
	 * @var Array Document data.
	 */
	protected $data = array();

	/**
	 * Load shared configuration values.
	 * 
	 * @param Array $config Document configuration.
	 */
	public function load(Array $config) {
		if (isset($config['attributes']) && sizeof($config['attributes'])) {
			$this->setData('attributes', array_merge(
				$this->getData('attributes'), $config['attributes']
			));
		}
	}

	/**
	 * Merge shared configuration.
	 * 
	 * @param AbstractDocument $document Document instance.
	 */
	public function merge(AbstractDocument $document) {
		$this->setData('attributes', array_merge(
			$this->getData('attributes'), $document->getData('attributes')
		));
	}

	/**
	 * Reset shared document configuration.
	 * 
	 * @param bool $preserveDetails Optional. Preserve details.
	 */
	public function reset($preserveDetails = false) {
		$this->setData('attributes', []);
	}

	/**
	 * Get data value.
	 * 
	 * @param string $key The data key.
	 * 
	 * @return mixed The data value, null if non-existent.
	 */
	public function getData($key) {
		if ($this->hasData($key)) {
			return $this->data[$key];
		}
	}

	/**
	 * Set data value.
	 * 
	 * @param string $key The data key.
	 * @param mixed $value The data value.
	 */
	public function setData($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * Check if data exists.
	 * 
	 * @param string $key The data key.
	 * 
	 * @return bool True if data exists, false otherwise.
	 */
	public function hasData($key) {
		return array_key_exists($key, $this->data);
	}

	/**
	 * Remove data.
	 * 
	 * @param string $key Data key.
	 */
	public function removeData($key) {
		unset($this->data[$key]);
	}

	/**
	 * Get attribute.
	 * 
	 * @param string $key Attribute key.
	 * 
	 * @return mixed Attribute value, null if non-existent.
	 */
	public function getAttribute($key) {
		if ($this->hasAttribute($key)) {
			return $this->data['attributes'][$key];
		}
	}

	/**
	 * Set attribute value.
	 * 
	 * @param string $key Attribute key.
	 * @param string $value Attribute value.
	 */
	public function setAttribute($key, $value) {
		$this->data['attributes'][$key] = $value;
	}

	/**
	 * Check if attribute exists.
	 * 
	 * @param string $key Attribute key.
	 * 
	 * @return bool True if attribute exists, false otherwise.
	 */
	public function hasAttribute($key) {
		return array_key_exists($key, $this->data['attributes']);
	}

	/**
	 * Remove attribute.
	 * 
	 * @param string $key Attribute key.
	 */
	public function removeAttribute($key) {
		if ($this->hasAttribute($key)) {
			unset($this->data['attributes'][$key]);
		}
	}

	/**
	 * Get classes.
	 * 
	 * @return string Class string.
	 */
	public function getClasses() {
		if ($this->hasAttribute('class')) {
			return $this->data['attributes']['class'];
		}
		else
			return '';
	}

	/**
	 * Set classes.
	 * 
	 * @param string $classes Class string.
	 */
	public function setClasses($classes) {
		$this->data['attributes']['class'] = $classes;
	}

	/**
	 * Add class.
	 * 
	 * @param string $class Class name.
	 */
	public function addClass($class) {
		if (!$this->hasAttribute('class')) {
			$this->setAttribute('class', '');
		}
		$this->data['attributes']['class'] .= (strlen($this->data['attributes']['class']) ? ' ' : '') . $class;
	}

	/**
	 * Check if class exists.
	 * 
	 * @param string $class Class to check for.
	 * 
	 * @return bool True if class exists, false otherwise.
	 */
	public function hasClass($class) {
		return $this->hasAttribute('class') && strpos(" {$this->getAttribute('class')} ", $class) !== false;
	}

	/**
	 * Remove a class.
	 * 
	 * @param string $class Class name to remove.
	 */
	public function removeClass($class) {
		if ($this->hasAttribute('class')) {
			$classes = " {$this->getClasses()} ";
			$classes = str_replace(" {$class} ", ' ', $classes);
			$this->setClasses($classes);
		}
	}

	/**
	 * Render attributes.
	 * 
	 * @return string Rendered attributes.
	 */
	public function renderAttributeList($attributes) {
		$string = '';
		foreach ($attributes as $key => $value) {
			$string .= ' ' . ($value !== null
				? "{$key}=\"" . htmlentities($value) . '"'
				: "{$key}");
		}
		return $string;
	}

	/**
	 * Default configuraation values.
	 * 
	 * @return Array Configuration values.
	 */
	protected function defaults() {
		return [
			'attributes' => []
		];
	}
}