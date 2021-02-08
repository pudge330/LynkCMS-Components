<?php
namespace BGStudios\Component\Package\Processors;

class PostCssProcess extends CompilerProcess {
	public function run($config) {
		$files = $config->get('files');
		if ($files) {
			$this->output("Running process: <fg=white>postcss</>");
			$packageName = $this->helpers->getPackageName();
			foreach ($files as $file) {
				$filePath = $this->sourceDir . $file;
				$this->output("\t>> <fg=cyan>@{$packageName}/{$file}</>");
				$output = shell_exec("postcss --use autoprefixer {$filePath} --autoprefixer.browsers \"> 2%\" -o {$filePath} --verbose 2>&1");
				$output = str_replace(["2%\""], ["2%\"\n"], $output); //!--not sure why this is here?
				$output = trim($output);
				if (strlen($output) > 0)
					$this->output("\n{$output}\n");
			}
			$this->output('');
		}
	}
}