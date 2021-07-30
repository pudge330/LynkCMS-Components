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
 * @subpackage ApplicationCompiler
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace LynkCMS\Component\ApplicationCompiler;

use Exception;
use LynkCMS\Component\ApplicationCompiler\Event as CompilerEvent;
use LynkCMS\Component\ApplicationCompiler\SubCompiler;
use LynkCMS\Component\Config\Config;
use LynkCMS\Component\Config\ConfigLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Base class for compiling an application or set of application files
 * based on configuration files.
 */
class AppCompiler {
	/**
	 * Root of the project/used for configuration loading as well.
	 *
	 * @var string $root
	 */
	protected $root;

	/**
	 * Environment of the build, `dev` or `prod`
	 *
	 * @var string $env
	 */
	protected $env;

	/**
	 * Temporary working directory.
	 *
	 * @var string $tmpDirectory
	 */
	protected $tmpDirectory;

	/**
	 * Application compilers, an array of
	 * LynkCMS\Component\ApplicationCompiler\SubCompiler\AbstractCompiler
	 * instances.
	 *
	 * @var array $compilers
	 */
	protected $compilers;

	/**
	 * Instance of Symfony's EventDispatcher class.
	 *
	 * @var object $eventDispatcher
	 */
	protected $eventDispatcher;

	/**
	 * @param string $root The projects root directory.
	 * @param string $tmpDirectory Temporary working directory.
	 * @param string $env Optional. Application environment.
	 * @param object $config Optional. Imstance of LynkCMS\Component\Config\Config class.
	 */
	public function __construct($root, $tmpDirectory, $env = null, $config = null) {
		$this->root = rtrim($root, '/');
		$this->env = $env;
		$this->eventDispatcher = new EventDispatcher();
		$this->tmpDirectory = ltrim($tmpDirectory);
		if (!preg_match('/^\//', $this->tmpDirectory))
			$this->tmpDirectory = "{$this->root}/$this->tmpDirectory";
		if (!file_exists($this->tmpDirectory) || !is_writable($this->tmpDirectory)) {
			throw new Exception(
				sprintf("AppCompiler::__construct() Error: temp directory must exist and be writable. \"{$this->tmpDirectory}\" provided.")
			);
		}
		$this->config = $config instanceof Config ? $config : new Config();
		$this->configLoader = new ConfigLoader($this->root, $this->config);
		$this->compilers = Array();
		$this->doSetup();
		$this->doRegisterListeners();
		$this->doRegisterSubscribers();
		$this->doCompilers();
	}
	/**
	 * Loads an array of config files.
	 *
	 * @param array $files Array of configuration file paths.
	 * 
	 * @return Array Configuration data.
	 */
	public function loadConfig($files) {
		$compilers = $this->compilers;
		$callback = function($key, &$config, $import) use ($compilers) {
			foreach ($compilers as $compiler) {
				$import['resource'] = $config;
				$compiler->configImport($key, $import);
				$config = $import['resource'];
			}
		};
		return $this->configLoader->load($files, $callback);
	}

	/**
	 * Compile files and write to destination path.
	 * 
	 * @param mixed $config Config as array or file path.
	 * @param Array $templateMap Optional. Template map of source and destination files.
	 * @param bool $configIsArray Optional. Whether or not config is already loaded into an array.
	 * @param Array $defaultConfig Optional. Default configuration.
	 */
	public function compile($config, $templateMap = null, $configIsArray = true, $defaultConfig = Array()) {
		$templateMap = !$templateMap ? $this->templateMap() : $templateMap;
		$config = $configIsArray ? $config : $this->loadConfig($config);
		$config = \bgs\deepMerge($defaultConfig, $config);

		foreach ($this->compilers as $compiler) {
			$compiler->config($config);
		}

		$event = new CompilerEvent\ConfigEvent($this->root, $this->env, $config);
		$this->eventDispatcher->dispatch(CompilerEvent\ConfigEvent::NAME, $event);
		$config = $event->getConfig();
		$vars = Array(
			'%root_var%' => $this->root
			,'%env_var%' => $this->env
			,'%envUcFirst_var%' => ucfirst($this->env)
		);

		foreach ($this->compilers as $compiler) {
			$compiler->compile($config, $vars);
		}

		$event = new CompilerEvent\CompileEvent($this->root, $this->env, $config, $vars);
		$this->eventDispatcher->dispatch(CompilerEvent\CompileEvent::NAME, $event);
		$vars = $event->getVars();

		$event = new CompilerEvent\InjectionEvent($this->root, $this->env, $config);
		foreach ($this->compilers as $compiler) {
			$compiler->injectionSetup($event, $config);
		}
		foreach ($this->compilers as $compiler) {
			$compiler->injection($event, $config);
		}
		$this->eventDispatcher->dispatch(CompilerEvent\InjectionEvent::NAME, $event);
		$injections = $event->getInjections();
		foreach ($injections as $ikey => $ival) {
			$vars[$ival['placeholder']] = $ival['content'];
		}

		$varKeys = array_keys($vars);
		$varValues = array_values($vars);
		$toCopy = Array();
		foreach ($templateMap as $templatePath => $templateDest) {
			$template = file_get_contents($templatePath);
			$template = str_replace($varKeys, $varValues, $template);
			if (!preg_match('/^\//', $templatePath))
				$templatePath = "{$this->root}/{$templatePath}";
			$dirname = dirname($templatePath);
			$tmpDestination = $this->getUniqueTemporaryPath('compiledTemplate-');
			if (!file_exists($dirname)) {
				if (!mkdir($dirname, 0755, true)) {
					throw new Exception("AppCompiler::compile() Error: could not create for folder for file \"{$templatePath}\".");
				}
			}
			if (file_put_contents($tmpDestination, $template) === false) {
				throw new Exception("AppCompiler::compile() Error: failed writing to tmp file \"{$tmpDestination}\".");
			}
			$toCopy[$tmpDestination] = $templateDest;
		}

		foreach ($toCopy as $tmp => $file) {
			copy($tmp, $file);
			unlink($tmp);
		}
	}

	/**
	 * Trim extra whitespace from template.
	 * Doesn't seemed to be used, possibly needs implemented or removed.
	 * 
	 * @param string $template Template to trim.
	 * 
	 * @return string Template code.
	 */
	protected function cleanUpTemplate($template) {
		$template = trim($template);
		return $template;
	}

	/**
	 * Load compilers and run SubCompiler event to modify or add addtional compilers.??
	 */
	protected function doCompilers() {
		$compilers = $this->compilers();
		if (is_array($compilers))
			$this->compilers = array_merge($this->compilers, $compilers);
		$event = new CompilerEvent\SubCompilerEvent($this->root, $this->env);
		$this->eventDispatcher->dispatch(CompilerEvent\SubCompilerEvent::NAME, $event);
		if ($event->hasSubCompilers()) {
			$this->compilers = array_merge($this->compilers, $event->getSubCompilers());
		}
	}

	/**
	 * Do any additonal setup required. For example setting context to config class.
	 */
	protected function doSetup() {
		$this->config->setToken('compiler.root', $this->root);
		$this->setup();
	}

	/**
	 * Load event listeners.
	 */
	protected function doRegisterListeners() {
		$listeners = $this->registerListeners();
		if (is_array($listeners)) {
			foreach ($listeners as  $listener) {
				$this->eventDispatcher->addListener($listener[0], $listener[1]);
			}
		}
	}

	/**
	 * Load event subscribers.
	 */
	protected function doRegisterSubscribers() {
		$subscribers = $this->registerSubscribers();
		if (is_array($subscribers)) {
			foreach ($subscribers as  $subscriber) {
				$this->eventDispatcher->addSubscriber($subscriber);
			}
		}
	}

	/**
	 * Overrideable method to setup compilers.
	 * 
	 * @return Array List of compilers.
	 */
	protected function compilers() { return Array(); }

	/**
	 * Overrideable method to setup event listeners.
	 * 
	 * @return Array List of event listeners.
	 */
	protected function registerListeners() { return Array(); }

	/**
	 * Overrideable method to setup event subscribers.
	 * 
	 * @return Array List of event subscribers.
	 */
	protected function registerSubscribers() { return Array(); }

	/**
	 * Overrideable method to do additional setup.
	 */
	protected function setup() {}

	/**
	 * Overrideable method to configure template map.
	 * 
	 * @return Array Template map as source and matching destination paths.
	 */
	protected function templateMap() { return Array(); }

	/**
	 * Get unique temporary path for writing to.
	 * 
	 * @return string File path.
	 */
	public function getUniqueTemporaryPath($prefix = '') {
		$path = '';
		do {
			$path = $this->tmpDirectory . $prefix . md5(\bgs\getRandomBytes(128));
		} while (file_exists($path));
		return $path;
	}
}
