<?php
namespace LynkCMS\Component\SiteCrawler\Link;

class LinkStorage {
	protected $folder;
	protected $prefix;
	public function __construct($folder, $prefix) {
		$this->folder = $folder;
		$this->prefix = $prefix;
	}
	protected function getFiles() {
		$files = glob("{$this->folder}/{$this->prefix}-*.txt");
		sort($files, SORT_STRING);
		return $files;
	}
	public function get() {
		$files = $this->getFiles();
		if (!sizeof($files)) {
			return null;
		}
		$url = null;
		while (sizeof($files) && !$url) {
			$file = array_pop($files);
			$content = trim(file_get_contents($file));
			$lines = explode("\n", $content);
			if (!sizeof($lines)) {
				unlink($file);
			}
			else {
				while (sizeof($lines) && !$url) {
					$line = array_pop($lines);
					if ($line && trim($line) != '') {
						$url = $line;
					}
				}
				if (!sizeof($lines)) {
					unlink($file);
				}
				else {
					$content = implode("\n", $lines);
					file_put_contents($file, $content . "\n");
				}
			}
		}
		return $url;
	}
	public function set($url) {
		$url = trim($url);
		$files = $this->getFiles();
		if (!sizeof($files)) {
			file_put_contents("{$this->folder}/{$this->prefix}-1.txt", "{$url}\n");
		}
		else {
			$file = array_pop($files);
			$lineCount = sizeof(explode("\n", file_get_contents($file)));
			if ($lineCount > 999) {
				preg_match('/(\d+)\.txt$/', $file, $match);
				$fileCount = $match[1] + 1;
				file_put_contents(
					"{$this->folder}/{$this->prefix}-{$fileCount}.txt",
					""
				);
				$file = "{$this->folder}/{$this->prefix}-{$fileCount}.txt";
			}
			file_put_contents($file, "{$url}\n", FILE_APPEND);
		}
	}
	public function has($url) {
		$files = $this->getFiles();
		foreach ($files as $file) {
			$lines = explode("\n", file_get_contents($file));
			if (in_array($url, $lines) || in_array(rtrim($url, '/'), $lines)) {
				return true;
			}
		}
		return false;
	}
}