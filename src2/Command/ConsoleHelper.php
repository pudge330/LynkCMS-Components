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

namespace Lynk\Component\Command;

use Closure;

/**
 * Single-class with console helper functions for pure PHP console applications without 3rd-party libraries.
 */
class ConsoleHelper {

	/**
	 * @var string Current loading indicator state.
	 */
	protected $currentLoadingIndicatorState;

	/**
	 * @var Array Built-in console forground/text colors.
	 */
	protected $foregroundColors;

	/**
	 * @var Array Built-in console text background colors.
	 */
	protected $backgroundColors;

	/**
	 * @var int Max character count to autowrap output text.
	 */
	protected $autoOutputWrap;

	/**
	 * @var string Autowrap break charater(s).
	 */
	protected $autoOutputWrapBreak;

	/**
	 * @param bool $autoWrap Optional. Max character count to autowrap output text.
	 */
	public function __construct($autoWrap = null) {
		$this->setAutoWrap($autoWrap);
		$this->setAutoWrapBreak("\n");
		$this->currentLoadingIndicatorState = '/';
		// Set up shell colors
		$this->foregroundColors = [
			'black' => "\033[0;30m"
			,'dark_gray' => "\033[1;30m"
			,'blue' => "\033[0;34m"
			,'light_blue' => "\033[1;34m"
			,'green' => "\033[0;32m"
			,'light_green' => "\033[1;32m"
			,'cyan' => "\033[0;36m"
			,'light_cyan' => "\033[1;36m"
			,'red' => "\033[0;31m"
			,'light_red' => "\033[1;31m"
			,'purple' => "\033[0;35m"
			,'light_purple' => "\033[1;35m"
			,'brown' => "\033[0;33m"
			,'light_brown' => "\033[1;33m"
			,'light_gray' => "\033[0;37m"
			,'white' => "\033[1;37m"
		];
		$this->backgroundColors = [
			'black' => "\033[40m"
			,'red' => "\033[41m"
			,'green' => "\033[42m"
			,'brown' => "\033[43m"
			,'blue' => "\033[44m"
			,'magenta' => "\033[45m"
			,'cyan' => "\033[46m"
			,'light_gray' => "\033[47m"
		];
	}

	/**
	 * Set autowrap value.
	 *
	 * @param bool $autoWrap Optional. Max character count to autowrap output text.
	 * 
	 * @return ConsoleHelper Returns $this for method chaining.
	 */
	public function setAutoWrap($autoWrap) {
		$sysColMax = $this->getColumnCount();
		if (is_integer($autoWrap)) {
			if ($autoWrap > $sysColMax)
				$this->autoOutputWrap = $sysColMax;
			else
				$this->autoOutputWrap = $autoWrap;
		}
		else if (is_null($autoWrap)) {
			$this->autoOutputWrap = null;
		}
		return $this;
	}

	/**
	 * Set the autowrap break character(s).
	 *
	 * @param string $autoWrapBreak The character(s) use to break text in console, defaults to newline.
	 * 
	 * @return ConsoleHelper Returns $this for method chaining.
	 */
	public function setAutoWrapBreak($autoWrapBreak) {
		$this->autoOutputWrapBreak = $autoWrapBreak;
		return $this;
	}

	/**
	 * Write text to console on a newline.
	 *
	 * @param string $string The text to output.
	 * 
	 * @return ConsoleHelper Returns $this for method chaining.
	 */
	public function writeln($string) {
		if ($this->autoOutputWrap && strlen($string) > $this->autoOutputWrap)
			$string = $this->wordwrap($string, $this->autoOutputWrap, $this->autoOutputWrapBreak);
		echo "\n{$string}";
		return $this;
	}

	/**
	 * Write text to the console.
	 *
	 * @param string $string The text to output.
	 * 
	 * @return ConsoleHelper Returns $this for method chaining.
	 */
	public function write($string) {
		if ($this->autoOutputWrap)
			$string = $this->wordwrap($string, $this->autoOutputWrap, $this->autoOutputWrapBreak);
		echo $string;
		return $this;
	}

	/**
	 * Output a divider to the console.
	 *
	 * @param string $char The character to use for the divider.
	 * 
	 * @return ConsoleHelper Returns $this for method chaining.
	 */
	public function divider($char) {
		$max = $this->autoOutputWrap ?: $this->getColumnCount();
		echo str_repeat($char, $max);
		return $this;
	}

	/**
	 * Output a standardized header to the console.
	 *
	 * @param string $header The header text.
	 * @param string $char Optional. The filler text to make the header full width and center the text.
	 * 
	 * @return ConsoleHelper Returns $this for method chaining.
	 */
	public function header($header, $char = '*') {
		$len = strlen($header);
		return $this->writeLn(
			str_repeat($char, ceil(($this->columnMax() - $len) / 2)) . $header . str_repeat($char, floor(($this->columnMax() - $len) / 2))
		);
	}

	/**
	 * Ask a question.
	 *
	 * @param string $message The question to ask.
	 * @param string|null Optional. Default answer.
	 * 
	 * @return string|null The answer to the question.
	 */
	public function ask($message, &$answer = null) {
		return $this->getAnswer($message, $answer);
	}

	/**
	 * Ask a question that is required.
	 *
	 * @param string $message The question to ask.
	 * @param string|null Optional. Default answer.
	 * 
	 * @return string The answer to the question.
	 */
	public function askRequired($message, &$answer = null) {
		do {
			$this->getAnswer($message, $answer);
		} while ($answer !== '');
		return $answer;
	}

	/**
	 * Ask a question while hiding what the user is typing.
	 *
	 * @param string $message The question to ask.
	 * @param string|null Optional. Default answer.
	 * 
	 * @return string|null The answer to the question.
	 */
	public function askHidden($message, &$answer = null) {
		$this->getAnswer($message . "\033[0;30m\033[40m", $answer, function($str) { return trim($str, "\n"); });
		echo "\033[0m";
		return $answer;
	}

	/**
	 * Ask a required question while hiding what the user is typing.
	 *
	 * @param string $message The question to ask.
	 * @param string|null Optional. Default answer.
	 * 
	 * @return string The answer to the question.
	 */
	public function askHiddenRequired($message, &$answer = null) {
		do {
			$this->getAnswer($message . "\033[0;30m\033[40m", $answer, function($str) { return trim($str, "\n"); });
			echo "\033[0m";
		} while (!$answer);
		return $answer;
	}

	/**
	 * Output a progress bar.
	 *
	 * @param int $done The current progress.
	 * @param int $total The total value for the progress bar.
	 * @param int $length Optional. The length of characters of the bar.
	 */
	public function progressBar($done, $total, $length = 50) {
		$perc = floor(($done / $total) * 100);
		$lengthBeforeBar =
			11 +
			strlen((string)$perc) +
			strlen((string)$done) +
			strlen((string)$total)
		;
		$remainingBeforeBar = $this->autoOutputWrap ?: $this->getColumnCount();
		if ($this->autoOutputWrap > $this->getColumnCount())
			$remainingBeforeBar = $this->getColumnCount();
		$remainingBeforeBar = $remainingBeforeBar - $lengthBeforeBar;
		if ($remainingBeforeBar <= 0)
			$remainingBeforeBar = 10;
		$filler = '';
		if ($length > $remainingBeforeBar) {
			$length = $remainingBeforeBar;
		}
		else {
			$filler = str_repeat(' ', $remainingBeforeBar - $length);
		}
		$left = floor(($perc / 100) * $length);
		$equals = str_repeat('=', $left);
		$space = str_repeat('.', $length - $left);
		if (($length - $left) + $left + 1 < $length + 1)
			$space .= ' ';
		$write = sprintf("\033[0G\033[2K[{$equals}>{$space}]{$filler} - $perc%% - $done/$total", "", "");
		fwrite(STDERR, $write);
	}

	/**
	 * Progress bar with message.
	 *
	 * @param string $message The message to show for the current progress step.
	 * @param int $done The current progress.
	 * @param int $total The total value for the progress bar.
	 * @param int $length Optional. The length of characters of the bar.
	 */
	public function progressMessage($message, $done, $total, $indicator = true) {
		$perc = floor(($done / $total) * 100);
		$length = strlen($message);
		$lengthBeforeBar =
			1 + ($indicator ? 1 : 0) +
			8 +
			strlen((string)$perc) +
			strlen((string)$done) +
			strlen((string)$total)
		;
		$remainingBeforeBar = $this->autoOutputWrap ?: $this->getColumnCount();
		if ($this->autoOutputWrap > $this->getColumnCount())
			$remainingBeforeBar = $this->getColumnCount();
		$remainingBeforeBar = $remainingBeforeBar - $lengthBeforeBar;
		if ($remainingBeforeBar <= 0)
			$remainingBeforeBar = 10;
		$filler = '';
		if ($length > $remainingBeforeBar) {
			$length = $remainingBeforeBar;
			$message = substr($message, 0, $length);
		}
		else {
			$filler = str_repeat(' ', $remainingBeforeBar - $length);
		}
		if ($indicator)
			$write = sprintf("\033[0G\033[2K{$this->currentLoadingIndicatorState} $message{$filler} - $perc%% - $done/$total", "", "");
		else
			$write = sprintf("\033[0G\033[2K$message - $perc%% - $done/$total", "", "");
		fwrite(STDERR, $write);
		if ($this->currentLoadingIndicatorState == '/')
			$this->currentLoadingIndicatorState = '-';
		else if ($this->currentLoadingIndicatorState == '-')
			$this->currentLoadingIndicatorState = '\\';
		else if ($this->currentLoadingIndicatorState == '\\')
			$this->currentLoadingIndicatorState = '|';
		else
			$this->currentLoadingIndicatorState = '/';
		if ($done == $total) {
			$this->currentLoadingIndicatorState = '/';
		}
	}

	/**
	 * Progress with message only.
	 *
	 * @param string $message The message to show for the current progress step.
	 * @param int $done The current progress.
	 * @param int $total The total value for the progress bar.
	 * @param int $length Optional. The length of characters of the bar.
	 */
	public function progressMessage2($message, $done, $total, $indicator = true) {
		$perc = floor(($done / $total) * 100);
		$left = 100 - $perc;
		if ($indicator)
			$write = sprintf("\033[0G\033[2K{$this->currentLoadingIndicatorState} $message - $perc%% - $done/$total", "", "");
		else
			$write = sprintf("\033[0G\033[2K$message - $perc%% - $done/$total", "", "");
		fwrite(STDERR, $write);
		if ($this->currentLoadingIndicatorState == '/')
			$this->currentLoadingIndicatorState = '-';
		else if ($this->currentLoadingIndicatorState == '-')
			$this->currentLoadingIndicatorState = '\\';
		else if ($this->currentLoadingIndicatorState == '\\')
			$this->currentLoadingIndicatorState = '|';
		else
			$this->currentLoadingIndicatorState = '/';
		if ($done == $total) {
			$this->currentLoadingIndicatorState = '/';
		}
	}

	/**
	 * Gets the real character count, ignoring the special color characters.
	 *
	 * @param string $str The string to count characters of.
	 * 
	 * @return int The count of characters.
	 */
	public function strlen($str) {
		$strlen = strlen($str);
		$charCount = 0;
		$skip = 0;
		for ($i = 0; $i < $strlen; $i++) {
			if ($str[$i] == "\033") {
				$fgLen = 7;
				$bgLen = 5;
				$closeLen = 4;
				$fgCheck = $i + $fgLen < $strlen ? substr($str, $i, $fgLen) : null;
				$bgCheck = $i + $bgLen < $strlen ? substr($str, $i, $bgLen) : null;
				$closeCheck = $i + $closeLen < $strlen ? substr($str, $i, $closeLen) : null;
				if ($fgCheck && in_array($fgCheck, $this->foregroundColors)) {
					$skip = $fgLen;
				}
				else if ($bgCheck && in_array($bgCheck, $this->backgroundColors)) {
					$skip = $bgLen;
				}
				else if ($closeCheck && $closeCheck == "\033[0m") {
					$skip = $closeLen;
				}
			}
			if ($skip == 0) {
				$charCount++;
			}
			else {
				$skip--;
			}
		}
		return $charCount;
	}

	/**
	 * Wrap a string of text to a predetermined character count per line.
	 *
	 * @param string $str The string of text.
	 * @param int $count Optional. The total character count per line.
	 * @param string $break Optional. The string that breaks the line, defaults to newline.
	 * 
	 * @return string The wrapped text.
	 */
	public function wordwrap($str, $count = 75, $break = "\n") {
		$strlen = strlen($str);
		$final = '';
		$charCount = 0;
		$breakLastSpace = function() use (&$final, $break, $count) {
			$revStr = str_split($final);
			$revStr = array_reverse($revStr);
			$strlen = sizeof($revStr);
			for ($i = 0; $i < $strlen; $i++) {
				if ($i > $count) {
					$i = $strlen;
				}
				if ($revStr[$i] == ' ') {
					$revStr[$i] = $break;
					$revStr = array_reverse($revStr);
					$final = implode($revStr);
					return $i;
				}
			}
			if ($final[strlen($final) - 1] !== "\n" || $final[strlen($final) - 1] !== "\r")
				$final .= $break;
			exit;
		};
		$skip = 0;
		for ($i = 0; $i < $strlen; $i++) {
			if ($str[$i] == "\033") {
				$fgLen = 7;
				$bgLen = 5;
				$closeLen = 4;
				$fgCheck = $i + $fgLen < $strlen ? substr($str, $i, $fgLen) : null;
				$bgCheck = $i + $bgLen < $strlen ? substr($str, $i, $bgLen) : null;
				$closeCheck = $i + $closeLen < $strlen ? substr($str, $i, $closeLen) : null;
				if ($fgCheck && in_array($fgCheck, $this->foregroundColors)) {
					$skip = $fgLen;
				}
				else if ($bgCheck && in_array($bgCheck, $this->backgroundColors)) {
					$skip = $bgLen;
				}
				else if ($closeCheck && $closeCheck == "\033[0m") {
					$skip = $closeLen;
				}
			}
			if ($skip == 0) {
				$charCount++;
				if ($charCount > $count) {
					if (in_array($str[$i], [' ', "\n", "\r"])) {
						if ($str[$i] == ' ') {
							$str[$i] = $break . ' ';
						}
						else
							$str[$i] = $break;
						$charCount = 0;
					}
					else {
						$reversed = $breakLastSpace();
						$charCount = 1;
					}
				}
			}
			else {
				$skip--;
			}
			if (in_array($str[$i], ["\n", "\r"])) {
				$charCount = 0;
			}
			$final .= $str[$i];
		}
		return $final;
	}

	/**
	 * Get the console column count.
	 *
	 * @return int The console column count.
	 */
	public function getColumnCount() {
		return exec('tput cols');
	}

	/**
	 * Get the console row count.
	 *
	 * @return int The console row count.
	 */
	public function getRowCount() {
		return exec('tput lines');
	}

	/**
	 * Get the max column count.
	 *
	 * @return int The max column count.
	 */
	public function columnMax() {
		$colCount = $this->getColumnCount();
		$max = $this->autoOutputWrap ?: $colCount;
		if ($max > $colCount)
			$max = $colCount;
		return $max;
	}

	/**
	 * Return a OS name.
	 *
	 * @return string The OS name.
	 */
	public function operatingSystem() {
		$os = 'linux';
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$os = 'windows';
		}
		else if (strtoupper(PHP_OS) === 'DARWIN') {
			$os = 'mac';
		}
		return $os;
	}

	/**
	 * Colorize a string for UNIX based systems.
	 *
	 * @param string $string The string to colorize.
	 * @param string $fg Optional. The text foreground color name.
	 * @param string $bg Optional. The text background color name.
	 * 
	 * @return string The colorized text.
	 */
	public function colorize($string, $fg = null, $bg = null) {
		$colorized = "";
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			if (isset($this->foregroundColors[$fg])) {
				$colorized .= $this->foregroundColors[$fg];
			}
			if (isset($this->backgroundColors[$bg])) {
				$colorized .= $this->backgroundColors[$bg];
			}
			$colorized .=  $string;
			if (isset($this->foregroundColors[$fg]) || isset($this->backgroundColors[$fg]))
				$colorized .= "\033[0m";
		}
		else {
			$colorized = $string;
		}
		return $colorized;
	}

	/**
	 * Get the available foreground color names.
	 *
	 * @return Array The array of color names.
	 */
	public function getForegroundColors() {
		return array_keys($this->foregroundColors);
	}

	/**
	 * Get the available background color names.
	 *
	 * @return Array The array of color names.
	 */
	public function getBackgroundColors() {
		return array_keys($this->backgroundColors);
	}

	/**
	 * Get the user input for a question.
	 *
	 * @param string $message The question to ask.
	 * @param string|null Optional. Default answer.
	 * @param Closure|null Optional. A closure that will trim the string and return the final result.
	 * 
	 * @return string|null The answer to the question.
	 */
	protected function getAnswer($message, &$answer = null, Closure $trim = null) {
		if ($this->autoOutputWrap && strlen($message) > $this->autoOutputWrap)
			$message = $this->wordwrap($message, $this->autoOutputWrap, $this->autoOutputWrapBreak);
		echo $message;
		$h = fopen('php://stdin', 'r');
		$answer = fgets($h);
		fclose($h);
		if (!$trim)
			$trim = function($str) { return trim($str); };
		if ($trim instanceof Closure)
			$answer = $trim($answer);
		return $answer;
	}
}