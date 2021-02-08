<?php
namespace BGStudios\Component\Package\Processors;

class SassProcess extends CompilerProcess {
	public function run($config) {
		$from = $config->get('from');
		$to = $config->get('to') ?: str_replace('.scss', '.css', $from);

		$lineNumbers = $config->get('lineNumbers');
		$minify = $config->get('minify');
		$noMap = $config->get('noMap');
		$cmdOptions = $config->get('cmdOptions');

		if ($to && $from) {

			$this->output("Running process: <fg=white>sass</>");

			$fromPath = $this->sourceDir . $from;
			$toPath = $this->publicDir . $to;

			$options = '';

			$addOption = function($opt) use (&$options) {
				if ($options != '')
					$options .= ' ';
				$options .= $opt;
			};

			if ($minify && LYNK_ENV == 'prod')
				$addOption('--style=compressed');
			else
				$addOption('--style=expanded');
			// if ($lineNumbers && LYNK_ENV == 'dev')
			// 	$addOption('--line-numbers');
			if ($noMap)
				$addOption('--no-source-map');
				// $addOption('--sourcemap=none');
			if ($cmdOptions)
				$addOption($cmdOptions);

			if (!file_exists(dirname($toPath)))
				mkdir(dirname($toPath), 0755, true);

			$packageName = $this->helpers->getPackageName();
			$this->output("\t<fg=yellow>@{$packageName}/{$from}</> >> <fg=cyan>@{$packageName}/{$to}</>\n");

			$output = shell_exec("sass {$fromPath}:{$toPath} {$options}");

			$output = str_replace(["2%\""], ["2%\"\n"], $output); //!--not sure why this is here?
			$output = trim($output);

			echo $output;
			//!--need to catch sass output instead
			// if (strlen($output) > 0)
			// 	$this->output("\n{$output}\n\n");

		}
	}
}