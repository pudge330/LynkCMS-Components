<?php
namespace LynkCMS\Component\Package\Processors;

class ConcatProcess extends CompilerProcess {
	public function run($config) {
		$name = $config->get('name');
		$source = $config->get('source');
		if ($name && $source) {
			$this->output("Running process: <fg=white>concat</>");
			list($returnedSource, $returnedTemporary) = $this->helpers->resolveSources($this->sourceDir, $source);
			$dirname = dirname($this->publicDir . $name);
			if (!file_exists($dirname))
				mkdir($dirname, 0755, true);
			$finalName = $this->publicDir . $name;
			$tmpFinalName = $this->publicDir . $name . '.tmp';
			$fhandle = fopen($tmpFinalName, 'w');
			foreach ($returnedSource as $file) {
				if ($file && !empty($file) && file_exists($this->sourceDir . $file)) {
					fwrite($fhandle, file_get_contents($this->sourceDir . $file) . "\n");
				}
				else if ($file && !empty($file) && file_exists($file)) {
					fwrite($fhandle, file_get_contents($file) . "\n");
				}
			}
			fclose($fhandle);
			rename($tmpFinalName, $finalName);
			$fileCount = sizeof($returnedSource);
			$packageName = $this->helpers->getPackageName();
			$this->output("\t[<fg=yellow>{$fileCount} files</>] >> <fg=cyan>@{$packageName}/{$name}</>\n");
			foreach ($returnedTemporary as $file) {
				if (file_exists($file))
					unlink($file);
			}
		}
	}
}