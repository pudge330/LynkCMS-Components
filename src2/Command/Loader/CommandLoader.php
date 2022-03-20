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

use Closure;
use Symfony\Component\Console\Application;

/**
 * Console utility class to autoload commands from a given list of directories and optional namespaces.
 */
class CommandLoader {

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
	 * @param Symfony\Compnent\Console\Application Instance of Symfony Application class.
	 */
	public function __construct($root, Application $application) {
		$this->root = rtrim($root, '/');
		$this->application = $application;
	}

	/**
	 * Load commands from a directory.
	 *
	 * @param Array|LoaderSource $sources Array of directories to look for commands.
	 * @param Array|string|Closure $excluded Optional. A regex string, callback or array of regexes and callback that
	 *        will determine if the command should be excldued from loading.
	 * @param Closure $callback Callback to run when a command loaded.
	 */
	public function load(Array $sources, $excluded = null, $callback = null) {
		$commands = Array();
		foreach ($sources as $source) {
			if (!($source instanceof LoaderSource)) {
				$namespace = $root = null;
				if (!is_array($source)) {
					$source = [$source];
				}
				if (sizeof($source) !== 2) {
					$source[] = null;
				}
				$source = new LoaderSource($this->root, $source[0], $source[1]);
			}
			$commands = array_merge($commands, $source->getCommands($excluded));
		}
		$commands = array_unique($commands);
		foreach ($commands as $command) {
			$cmd = new $command();
			if ($callback) {
				call_user_func($callback, $cmd);
			}
			$this->application->add($cmd);
		}
	}
}