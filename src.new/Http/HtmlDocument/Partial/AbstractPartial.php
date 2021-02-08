<?php
namespace LynkCMS\Component\Http\HtmlDocument\Partial;

use LynkCMS\Component\Http\HtmlDocument\AbstractDocument;
use LynkCMS\Component\Http\HtmlDocument\Document;
use LynkCMS\Component\Http\HtmlDocument\Partial;

class AbstractPartial extends AbstractDocument {
	public function load(Array $config) {
		parent::load($config);
		if (isset($config['scripts']) && sizeof($config['scripts'])) {
			foreach ($config['scripts'] as $key => $script) {
				if (preg_match('/^\[file\]/', $script)) {
					$script = preg_replace('/^\[file\]/', '', $script);
					$script = "<script src=\"{$script}\"></script>";
				}
				else if (preg_match('/^\[code\]/', $script)) {
					$script = preg_replace('/^\[code\]/', '', $script);
					$script = "<script>{$script}</script>";
				}
				if (is_numeric($key)) {
					if (!$this->hasScript($script)) { 
						$this->setScript($script);
					}
				}
				else {
					$this->setScript($key, $script);
				}
			}
		}
		if (isset($config['scriptCode']) && sizeof($config['scriptCode'])) {
			foreach ($config['scriptCode'] as $key => $script) {
				if (is_numeric($key)) {
					if (!$this->hasScriptCode($script)) {
						$this->setScriptCode($script);
					}
				}
				else {
					$this->setScriptCode($key, $script);
				}
			}
		}
		if (isset($config['extra']) && sizeof($config['extra'])) {
			foreach ($config['extra'] as $key => $extra) {
				if (is_numeric($key)) {
					if (!$this->hasExtra($extra)) {
						$this->setExtra($extra);
					}
				}
				else {
					$this->setExtra($key, $extra);
				}
			}
		}
	}
	public function merge(AbstractDocument $document) {
		parent::merge($document);
		$scripts = $document->getData('scripts');
		foreach ($scripts as $key => $script) {
			if (is_numeric($key)) {
				if (!$this->hasScript($script)) {
					$this->setScript($script);
				}
			}
			else {
				$this->setScript($key, $script);
			}
		}
		$scriptCode = $document->getData('scriptCode');
		foreach ($scriptCode as $key => $script) {
			if (is_numeric($key)) {
				if (!$this->hasScriptCode($script)) {
					$this->setScriptCode($script);
				}
			}
			else {
				$this->setScriptCode($key, $script);
			}
		}
		$extraCode = $document->getData('extra');
		foreach ($extraCode as $key => $extra) {
			if (is_numeric($key)) {
				if (!$this->hasScriptCode($extra)) {
					$this->setScriptCode($extra);
				}
			}
			else {
				$this->setScriptCode($key, $extra);
			}
		}
	}
	public function reset($preserveDetails = false) {
		parent::reset($preserveDetails);
		$this->setData('scripts', []);
		$this->setData('scriptCode', []);
		$this->setData('extra', []);
	}
	public function getScript($key) {
		$scripts = $this->getData('scripts');
		if (array_key_exists($key, $scripts)) {
			return $scripts[$key];
		}
		else if ($scriptKey = array_search($key, $scripts)) {
			return $scripts[$scriptKey];
		}
	}
	public function setScript($var1, $var2 = null) {
		if (is_array($var1)) {
			foreach ($var1 as $key => $val) {
				if (!is_numeric($key)) {
					$this->setScript($val);
				}
				else {
					$this->setScript($key, $val);
				}
			}
		}
		else {
			if ($var2) {
				$this->data['scripts'][$var1] = $var2;
			}
			else {
				$this->data['scripts'][] = $var1;
			}
		}
	}
	public function hasScript($key) {
		$scripts = $this->getData('scripts');
		if (array_key_exists($key, $scripts)) {
			return true;
		}
		else if (array_search($key, $scripts)) {
			return true;
		}
		return false;
	}
	public function removeScript($key) {
		$scripts = $this->getData('scripts');
		if (array_key_exists($key, $scripts)) {
			unset($scripts[$key]);
		}
		else if ($scriptKey = array_search($key, $scripts)) {
			unset($scripts[$scriptKey]);
		}
		$this->setData('scripts', $scripts);
	}
	public function getScriptCode($key) {
		$scripts = $this->getData('scriptCode');
		if (array_key_exists($key, $scripts)) {
			return $scripts[$key];
		}
		else if ($scriptKey = array_search($key, $scripts)) {
			return $scripts[$scriptKey];
		}
	}
	public function setScriptCode($var1, $var2 = null) {
		if (is_array($var1)) {
			foreach ($var1 as $key => $val) {
				if (!is_numeric($key)) {
					$this->setScriptCode($val);
				}
				else {
					$this->setScriptCode($key, $val);
				}
			}
		}
		else {
			if ($var2) {
				$this->data['scriptCode'][$var1] = $var2;
			}
			else {
				$this->data['scriptCode'][] = $var1;
			}
		}
	}
	public function hasScriptCode($key) {
		$scripts = $this->getData('scriptCode');
		if (array_key_exists($key, $scripts)) {
			return true;
		}
		else if (array_search($key, $scripts)) {
			return true;
		}
		return false;
	}
	public function removeScriptCode($key) {
		$scripts = $this->getData('scriptCode');
		if (array_key_exists($key, $scripts)) {
			unset($scripts[$key]);
		}
		else if ($scriptKey = array_search($key, $scripts)) {
			unset($scripts[$scriptKey]);
		}
		$this->setData('scriptCode', $scripts);
	}
	public function getExtra($key) {
		$extras = $this->getData('extra');
		if (array_key_exists($key, $extras)) {
			return $extras[$key];
		}
		else if ($extraKey = array_search($key, $extras)) {
			return $extras[$extraKey];
		}
	}
	public function setExtra($var1, $var2 = null) {
		if (is_array($var1)) {
			foreach ($var1 as $key => $val) {
				if (!is_numeric($key)) {
					$this->setExtra($val);
				}
				else {
					$this->setExtra($key, $val);
				}
			}
		}
		else {
			if ($var2) {
				$this->data['extra'][$var1] = $var2;
			}
			else {
				$this->data['extra'][] = $var1;
			}
		}
	}
	public function hasExtra($key) {
		$extras = $this->getData('extra');
		if (array_key_exists($key, $extras)) {
			return true;
		}
		else if (array_search($key, $extras)) {
			return true;
		}
		return false;
	}
	public function removeExtra($key) {
		$extras = $this->getData('extra');
		if (array_key_exists($key, $extras)) {
			unset($extras[$key]);
		}
		else if ($extraKey = array_search($key, $extras)) {
			unset($extras[$extraKey]);
		}
		$this->setData('extra', $extras);
	}
	protected function defaults() {
		return array_merge(parent::defaults(), [
			'scripts' => [],
            'scriptCode' => [],
            'extra' => []
		]);
	}
}