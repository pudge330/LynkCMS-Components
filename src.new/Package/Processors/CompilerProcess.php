<?php
namespace LynkCMS\Component\Package\Processors;

use Closure;
use Exception;
use LynkCMS\Component\Command\ConsoleHelper;
use LynkCMS\Component\Container\ContainerAwareClass;
use Symfony\Component\Console\Output\OutputInterface;

class CompilerProcess extends ContainerAwareClass {
	protected $rootDir;
	protected $sourceDir;
	protected $publicDir;
	protected $output;
	protected $helpers;
	public function __construct($paths, $helpers, $output) {
		if (!$paths)
			throw new Exception("Root directory is required to instantiate a new instance of CompilerProcess");
		if (is_string($paths))
			$paths = ['root' => $paths];
		$paths = array_merge([
			'root' => null
			,'source' => null
			,'public' => null
		], $paths);
		$this->rootDir = $paths['root'];
		if (!$this->rootDir)
			throw new Exception("Root directory is required to instantiate a new instance of CompilerProcess");
		$this->sourceDir = $paths['source'];
		if (!$this->sourceDir)
			$this->sourceDir = $this->rootDir;
		$this->publicDir = $paths['public'];
		if (!$this->publicDir)
			$this->publicDir = $this->rootDir;
		$this->helpers = $helpers;
		$this->output = $output;
	}
	public function run($config) {}
	public function output($out, $method = 'writeln') {
		if (!$method)
			$method = 'writeln';
		if ($this->output) {
			if ($this->output instanceof Closure) {
				$func = $this->output;
				$func($out);
			}
			else if ($this->output instanceof OutputInterface || $this->output instanceof ConsoleHelper) {
				$this->output->{$method}($out);
			}
		}
	}
	
}