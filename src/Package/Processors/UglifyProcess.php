<?php
namespace BGStudios\Component\Package\Processors;

class UglifyProcess extends CompilerProcess {
	public function run($config) {
		$files = $config->get('files');
		if ($files && LYNK_ENV == 'prod') {
			$this->output("Running process: <fg=white>uglify</>");
			$packageName = $this->helpers->getPackageName();
			foreach ($files as $file) {
				$filePath = $this->publicDir . $file;
				$this->output("\t>> <fg=cyan>@{$packageName}/{$file}</>");
				$output = shell_exec("uglifyjs {$filePath} -c -m --comments -o {$filePath} 2>&1");
				$output = str_replace(["2%\""], ["2%\"\n"], $output); //!--not sure why this is here?
				$output = trim($output);
				// if (strlen($output) > 0)
				// 	$this->output("\n{$output}\n");
			}
			$this->output('');
		}
	}
}