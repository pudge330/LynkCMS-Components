<?php
namespace Lynk\Component\ScriptBuilder;

use Exception;
use Lynk\Component\ScriptBuilder\Event as BuildEvent;
use Lynk\Component\ScriptBuilder\SubBuilder;
use Lynk\Component\Config\Config;
use Lynk\Component\Config\ConfigLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ScriptBuilder {
	/**
	 * Root of the project/used for configuration loading as well.
	 *
	 * @var string $root
	 */
	protected $root;

	/**
	 * Environment of the build, `dev` or `prod`
	 *
	 * @var string $environment
	 */
	protected $environment;

	/**
	 * Temporary working directory.
	 *
	 * @var string $temporaryDirectory
	 */
	protected $temporaryDirectory;

	/**
	 * Application compilers, an array of
	 * Lynk\Component\ApplicationCompiler\SubCompiler\AbstractCompiler
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
	 * @param string $temporaryDirectory Temporary working directory.
	 * @param string $env Optional. Application environment.
	 * @param object $config Optional. Instance of Lynk\Component\Config\Config class.
	 */
	public function __construct(string $root, string $temporaryDirectory, string $environment, Config $configuration) {
		$this->root = \lynk\normalizePath($root);
		
	}

	
}