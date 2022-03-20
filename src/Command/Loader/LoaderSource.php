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
 * @subpackage Command
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Command\Loader;

class LoaderSource {

	/**
	 * @var string Root directory for relative path.
	 */
	protected $root;

	/**
	 * @var string Source directory path, absolute or relative.
	 */
	protected $src;

	/**
	 * @var string Optional. Namespace for loaded commands.
	 */
	protected $namespace;

	/**
	 * string Final/absolute directory path to search.
	 */
	protected $searchPath;

	/**
	 * @param string $root Root directory for relative paths.
	 * @param string $src Source directory path.
	 * @param string $namespace Optional. Command namespace.
	 */
	public function __construct($root, $src, $namespace = null) {
		$this->root = $root;
		$this->src = $src;
		$this->namespace = $namespace;
		$this->searchPath = preg_match('/^\//', $this->src) ? $this->src : "{$this->root}/{$this->src}";
	}

	/**
	 * Find and return all files from searchable path.
	 * 
	 * @return Array List of files found.
	 */
	protected function findFiles() {
		if (file_exists($this->searchPath) && is_dir($this->searchPath)) {
			return \lynk\getDirContents($this->searchPath);
		}
	}

	/**
	 * Return a list of commands with full namespace to class.
	 * 
	 * @param Array|string|Closure $excluded Optional. A regex string, callback or array of regexes and callback that
	 * 
	 * @return Array List of commands.
	 */
	public function getCommands($excluded = null) {
		$files = $this->findFiles();
		$commands = [];
		foreach ($files as $file) {
			$sourcePaths = ($this->namespace
				? Array(
					Array(\lynk\realRelPath($this->searchPath), $this->namespace)
					,Array(realpath($this->searchPath), $this->namespace)
					,Array($this->searchPath, $this->namespace)
				)
				: Array(
					\lynk\realRelPath($this->searchPath)
					,realpath($this->searchPath)
					,$this->searchPath
				)
			);
			$sourcePaths[] = '.php';
			$command = $this->trimSourcePath($file, $sourcePaths);
			$command = trim($command, '/');
			$command = str_replace('/', '\\', $command);
			if (!is_dir($file)
				&& (preg_match('/Command$/', $command)
					&& preg_match('/Command\.php$/', $file))
				&& !$this->isExcluded($file, $excluded)
				&& !$this->isExcluded($command, $excluded)
			) {
				$commands[] = "\\" . preg_replace('/^\\\/', '', $command);
			}
		}
		return $commands;
	}

	/**
	 * Determine whether or not the command should be loaded.
	 *
	 * @param string $file The file path of the command.
	 * @param Array|string|Closure $excluded Optional. A regex string, callback or array of regexes and callback that
	 *        will determine if the command should be excldued from loading.
	 * 
	 * @return bool Returns true if command should not be loaded or false if it should.
	 */
	protected function isExcluded($file, $excluded) {
		if ($excluded) {
			$excluded = is_array($excluded) ? $excluded : Array($excluded);
			foreach ($excluded as $e) {
				if ($e instanceof Closure) {
					$result = $e($file);
					if ($result === true)
						return true;
				}
				else if ($e && preg_match($e, $file))
					return true;
			}
		}
		return false;
	}

	/**
	 * Trims the base directory path from the command to get the commands full class name.
	 *
	 * @param string $file The file path of the command.
	 * @param Array $sources An array of base paths and parts needing removed from file path to get the full class name.
	 * 
	 * @return string The trimmed class name.
	 */
	protected function trimSourcePath($file, $sources) {
		foreach ($sources as $k => $v) {
			if (is_array($v)) {
				$file = str_replace(rtrim($v[0], '/'), rtrim($v[1], '\\'), $file);
			}
			else {
				$file = str_replace(rtrim($v, '/'), '', $file);
			}
		}
		return $file;
	}
}