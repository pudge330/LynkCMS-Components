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
 * @subpackage Command
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\Command;

use Closure;
use Symfony\Component\Console\Application;

/**
 * Console utility class to autoload commands from a given list of directory.
 */
class ConsoleCommandLoader {

	/**
	 * @var string Root directory for relative paths.
	 */
	protected $root;

	/**
	 * @var Symfony\Component\Console\Application Instance of Symfony Application class.
	 */
	protected $application;

	/**
	 * @param string $root Root directory for relative paths.
	 * @param Symfony\Compnent\Console\Application Optional. Instance of Symfony Application class.
	 */
	public function __construct($root, Application $application = null) {
		$this->root = rtrim($root, '/');
		$this->application = $application;
	}

	/**
	 * Load commands from a directory.
	 *
	 * @param Array $paths Array of directories to look for commands.
	 * @param Array|string|Closure $excluded Optional. A regex string, callback or array of regexes and callback that
	 *        will determine if the command should be excldued from loading.
	 * @param Symfony\Compnent\Console\Application Optional. Instance of Symfony Application class.
	 * @param Closure $callback Callback to run when a command loaded.
	 * 
	 * @return Array|null Returns an array if $application is set and not a Symfony\Component\Console\Application instance.
	 */
	public function load($paths, $excluded = null, Application $application = null, $callback = null) {
		$paths = is_array($paths) ? $paths : Array($paths);
		$files = $sources = $commands = Array();
		foreach ($paths as $path) {
			$namespace = null;
			if (is_array($path)) {
				$namespace = $path[1];
				$path = $path[0];
			}
			$toSearch = preg_match('/^\//', $path) ? $path : "{$this->root}/{$path}";
			if (file_exists($toSearch) && is_dir($toSearch)) {
				$files = array_merge($files, \lynk\getDirContents($toSearch));
				$sources = array_merge(
					$sources
					,($namespace
						? Array(
							Array(\lynk\realRelPath($toSearch), $namespace)
							,Array(realpath($toSearch), $namespace)
							,Array($toSearch, $namespace)
						)
						: Array(
							\lynk\realRelPath($toSearch)
							,realpath($toSearch)
							,$toSearch
						)
					)
				);
			}
		}
		$sources[] = '.php';
		foreach ($files as $file) {
			$filename = basename($file);
			$command = $this->trimSourcePath($file, $sources);
			$command = trim($command, '/');
			$command = str_replace('/', '\\', $command);
			if (!is_dir($file)
				&& (preg_match('/Command$/', $command)
					&& preg_match('/Command\.php$/', $file))
				&& !$this->isExcluded($file, $excluded)
				&& !$this->isExcluded($command, $excluded)
			) {
				$commands[] = "\\{$command}";
			}
		}
		$application = $application ?: $this->application;
		if ($application && $application instanceof Application) {
			foreach ($commands as $command) {
				$cmd = new $command();
				if ($callback) {
					call_user_func($callback, $cmd);
				}
				$application->add($cmd);
			}
		}
		else {
			return $commands;
		}
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