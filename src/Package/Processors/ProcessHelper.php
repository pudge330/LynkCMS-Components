<?php
namespace BGStudios\Component\Package\Processors;

use LynkCMS\Component\Command\ConsoleHelper;
use Exception;
use ZipArchive;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessHelper {
	protected $environment;
	protected $packagePaths;
	protected $packageName;
	protected $outputInterface;

	public function __construct($pkgs, $pkgName, $output) {
		$this->packagePaths = $pkgs;
		$this->packageName = $pkgName;
		$this->outputInterface = $output;
	}

	public function getPackageName() {
		return $this->packageName;
	}

	protected function trimPrivatePaths($str) {
		$pieces = explode('/', $str);
		$pieces = array_reverse($pieces);
		$pool = [];
		foreach ($pieces as $piece) {
			$pool[] = $piece;
			if (preg_match('/^([a-zA-z0-9-_]+)Package$/', $piece)) {
				$pool = array_reverse($pool);
				$str = implode('/', $pool);
				$str = preg_replace('/Package\/(Resources\/' . $type . ')/', 'Package:' . $type, $str);
				return $str;
			}
		}
		$pieces = explode('Resources/' . $type, $str);
		if (sizeof($pieces) > 1) {
			$last = array_pop($pieces);
			return $type . $last;
		}
		$paths = [
			$this->packagePaths->scripts($this->packageName) => "{$this->packageName}:scripts:"
			,$this->packagePaths->styles($this->packageName) => "{$this->packageName}:styles:"
			,LYNK_ROOT . LYNK_DS . LYNK_VAR . '/tmp' => "tmp:"
			,LYNK_ROOT => "root:"
		];
		foreach ($paths as $p => $r) {
			$str = str_replace($p, $r, $str);
		}
		return $str;
	}

	public function resolveSources($root, $source, $matchCallback = null) {
		$sourceFiles = $temporaryFiles = [];
		foreach ($source as $src) {
			list($returnedSource, $returnedTemporary) = $this->resolveSource($root, $src, $matchCallback);
			$sourceFiles = array_merge($sourceFiles, $returnedSource);
			$temporaryFiles = array_merge($temporaryFiles, $returnedTemporary);
		}
		return [$sourceFiles, $temporaryFiles];
	}

	public function resolveSource($root, $source, $matchCallback = null) {
		$sourceFiles = [];
		$temporaryFiles = [];
		$root = rtrim($root, '/');
		if (!$matchCallback)
			$matchCallback = function($path) { return true; };
		if (preg_match('/^(\[folder\])(.+)$/', $source)) {
			$source = preg_replace('/^(\[folder\])(?:\/)?(.+)$/', "$2", $source);
			$folderPath = "{$root}/{$source}";
			if (is_dir($folderPath)) {
				$files = \lynk\getDirContents($folderPath);
				foreach ($files as $file) {
					if (!is_dir($file)) {
						$matchResult = $matchCallback($file);
						if ($matchResult !== false)
							$sourceFiles[] = $file;
					}
				}
			}
		}
		else if (preg_match('/^(\[zip\])(.+)$/', $source)) {
			$source = preg_replace('/^(\[zip\]))(?:\/)?(.+)$/', "$2", $source);
			$zipFilePath = "{$root}/{$source}";
			$zip = new ZipArchive;
			if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
				$tmpZipDirectory = $this->getUniqueTemporaryPath($zipFilePath, 'ziparchive-');
				if(!is_dir($tmpZipDirectory)){
					mkdir($tmpZipDirectory,  0755, true);
				}
				$zip->extractTo($tmpZipDirectory);
				$zip->close();
				$tmpExtractedFiles = \lynk\getDirContents($tmpZipDirectory);
				foreach ($tmpExtractedFiles as $file) {
					if (!is_dir($file)) {
						$matchResult = $matchCallback($file);
						if ($matchResult !== false) {
							$newFile = $this->getUniqueTemporaryPath($file, 'extractedfile-');
							copy($file, $newFile);
							$sourceFiles[] = $newFile;
							$temporaryFiles[] = $newFile;
						}
						unlink($file);
					}
				}
				\lynk\clrDir($tmpZipDirectory);
				rmdir($tmpZipDirectory);
			}
		}
		else if (preg_match('/^(\[web\])(.+)$/', $source)) {
			$source = preg_replace('/^(\[web\])(?:\/)?(.+)$/', "$2", $source);
			$tmpUrlResource = $this->getUniqueTemporaryPath($source, 'urlResource-');
			$resourceData = $this->getUrlResource($source);
			file_put_contents($tmpUrlResource, $resourceData);
			$sourceFiles[] = $tmpUrlResource;
			$temporaryFiles[] = $tmpUrlResource;
		}
		else if (preg_match('/^(\[webzip\])(.+)$/', $source)) {
			$source = preg_replace('/^(\[webzip\])(?:\/)?(.+)$/', "$2", $source);
			$tmpUrlResource = $this->getUniqueTemporaryPath($source, 'urlResource-');
			var_dump('***RESOURCE URL***: ' . $source);
			$resourceData = $this->getUrlResource($source);
			file_put_contents($tmpUrlResource, $resourceData);
			$zip = new ZipArchive;
			if ($zip->open($tmpUrlResource, ZipArchive::CREATE) === TRUE) {
				$tmpZipDirectory = $this->getUniqueTemporaryPath($tmpUrlResource, 'ziparchive-');
				if(!is_dir($tmpZipDirectory)){
					mkdir($tmpZipDirectory,  0755, true);
				}
				$zip->extractTo($tmpZipDirectory);
				$zip->close();
				unlink($tmpUrlResource);
				$tmpExtractedFiles = \lynk\getDirContents($tmpZipDirectory);
				foreach ($tmpExtractedFiles as $file) {
					if (!is_dir($file)) {
						$matchResult = $matchCallback($file);
						if ($matchResult !== false) {
							$newFile = $this->getUniqueTemporaryPath($file, 'extractedfile-');
							copy($file, $newFile);
							$sourceFiles[] = $newFile;
							$temporaryFiles[] = $newFile;
						}
						unlink($file);
					}
				}
				\lynk\clrDir($tmpZipDirectory);
				rmdir($tmpZipDirectory);
			}
			//--need to implement
		}
		else {
			$sourceFiles[] = $root . '/' . $source;
		}
		return [$sourceFiles, $temporaryFiles];
	}

	public function getUniqueTemporaryPath($sourcePath, $prefix = '') {
		$path = '';
		do {
			$path = LYNK_ROOT . LYNK_DS . LYNK_VAR . '/tmp/' . $prefix . md5(\lynk\getRandomBytes(64) . $sourcePath);
		} while (file_exists($path));
		return $path;
	}

	public function getUrlResource($url) {
		if (is_callable('curl_init')) { //--use curl
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_ENCODING ,"");
			$data = curl_exec($curl);
			curl_close($curl);
			$data = str_replace("\r\n", "\n", $data);
			return $data;
		}
		else { //--try to use file_get_contents
			try {
				$data = file_get_contents($url);
				return str_replace("\r\n", "\n", $data);
			}
			catch (\Exception $e) {
				return '';
			}
		}
	}

	protected function copyFiles($filesToCopy, $copiedCB = null) {
		$failed = $copied = $skipped = 0;
		//--loop through each set of files
		//--files should be an array if 2
		//--1st is original file
		//--2nd is new file path to copy to
		foreach ($filesToCopy as $files) {
			if (sizeof($files) == 2) {
				//--check if new path exists and create it if not
				$newFileDirName = dirname($files[1]);
				if (!file_exists($newFileDirName))
					mkdir($newFileDirName, 0755, true);//--used to be 0777, but I think that give everyone execute permissions, not sure!!!
				//--determine if copied file already exists
				//--only copy if not or older than source
				$shouldCopy = true;
				$sourceEditTime = filemtime($files[0]);
				if (file_exists($files[1])) {
					if ($sourceEditTime <= filemtime($files[1]))
						$shouldCopy = false;
				}
				$tmpSourceName = $this->trimPrivatePaths($files[0]);
				$tmpDestName = $this->trimPrivatePaths($files[1]);
				// list($tmpSourceName, $tmpDestName) = $this->trimPathsForOutput($files[0], $files[1]);
				if ($shouldCopy) {
					if (!copy($files[0], $files[1])) {
						$failed++;
						$this->output(
							"<fg=red;>x</>Failed copying\n".
							"\t@<fg=white>{$tmpSourceName}</> ->\n\t@<fg=white>{$tmpDestName}</>"
						);
					}
					else {
						$copied++;
						$this->output(
							"Successfully <fg=yellow>copied</>\n".
							"\t@<fg=white>{$tmpSourceName}</> ->\n\t@<fg=white>{$tmpDestName}</>"
						);
						if ($copiedCB instanceof \Closure)
							$copiedCB($files[1]);
					}
				}
				else {
					$skipped++;
					$this->output(
						"<fg=cyan>Skipped</> copying\n".
						"\t@<fg=white>{$tmpSourceName}</> ->\n\t@<fg=white>{$tmpDestName}</>"
					);
				}
				if (\lynk\startsWith($files[0], LYNK_ROOT . LYNK_DS . LYNK_VAR . '/tmp'))
					unlink($files[0]);
			}
		}
		if ($copied > 0)
			$this->output("Successfully copied {$copied} file(s)");
		if ($skipped > 0)
			$this->output("Skipped {$skipped} older file(s)");
		if ($failed > 0)
			$this->output("Failed to copy {$failed} file(s)");
	}

	protected function output($msg) {
		if ($this->outputInterface) {
			if ($this->outputInterface instanceof \Closure) {
				$func = $this->outputCB;
				$func($msg);
			}
			else if ($this->outputInterface instanceof OutputInterface || $this->outputInterface instanceof ConsoleHelper) {
				$this->outputInterface->writeln($msg);
			}
		}
	}
}