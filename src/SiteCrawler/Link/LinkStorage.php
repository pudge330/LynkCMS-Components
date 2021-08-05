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
 * @subpackage SiteCrawler
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\SiteCrawler\Link;

/**
 * Link storage class.
 */
class LinkStorage {

	/**
	 * @var string Folder where the link file is stored.
	 */
	protected $folder;

	/**
	 * @var string File prefix.
	 */
	protected $prefix;

	/**
	 * @param string $folder The fodler where the link files are stored.
	 * @param string $prefix The prefix of the files.
	 */
	public function __construct($folder, $prefix) {
		$this->folder = $folder;
		$this->prefix = $prefix;
	}

	/**
	 * Get array of files.
	 * 
	 * @return Array An array of LinkStorage files.
	 */
	protected function getFiles() {
		$files = glob("{$this->folder}/{$this->prefix}-*.txt");
		sort($files, SORT_STRING);
		return $files;
	}

	/**
	 * Retrieve URL from link files.
	 * 
	 * @return string A URL.
	 */
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

	/**
	 * Write a URL to a storage file.
	 * 
	 * @param string $url The URL to save.
	 */
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

	/**
	 * Check whether or not a URL already exists.
	 * 
	 * @return bool True if URL exists, false if not.
	 */
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