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
 * @subpackage Functions
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace lynk;

use DateTime;
use Exception;

/**
 * Empty functions class, used to autoload this file.
 */
class Functions {}

/**
 * ==================================================
 * Http and Request
 * ==================================================
 */

/**
 * Execute curl request.
 * fix: `headers` and `curlOptions` needs to be implemented and tested.
 * 
 * @param string $url Request URL.
 * @param Array $options Optional. Request options.
 *                       - type:        Request type.
 *                       - post_json:   Post data instead of GET parameters appended to URL.
 *                       - headers:     Http headers.
 *                       - curlOptions: Curl options.
 * 
 * @return Array Response body and header.
 */
function curlRequest($url, Array $options = [], Array $data = []) {
	$options = array_merge([
		'type' => 'GET',
		'post_json' => false,
		'headers' => [],
		'curlOptions' => []
	], $options);
	
	$requestType = $options['type'] ? strtoupper($options['type']) : 'GET';

	$curlOptions = [];
	$curlHeaders = [];

	if ($requestType === 'GET') {
		if (sizeof($data)) {
			$url = $url . (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
		}
	}
	else {
		$curlOptions[] = [CURLOPT_CUSTOMREQUEST, $requestType];
		if ($options['post_json']) {
			$data = json_encode($data);
			$curlHeaders[] = 'Content-Type: application/json';
			$curlHeaders[] = 'Content-Length: ' . strlen($data);
		}
		else {
			$data = http_build_query($data);
			$curlOptions[] = [CURLOPT_POST, 1];
		}
		$curlOptions[] = [CURLOPT_POSTFIELDS, $data];
	}
	$curlOptions[] = [CURLOPT_URL, $url];
	$curlOptions[] = [CURLOPT_RETURNTRANSFER, true];
	$curlOptions[] = [CURLOPT_HEADER, 1];

	$curl = curl_init();
	foreach ($curlOptions as $option) {
		curl_setopt($curl, $option[0], $option[1]);
	}
	curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);

	$returnedContent = curl_exec($curl);
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($returnedContent, 0, $headerSize);
    $body = substr($returnedContent, $headerSize);

    curl_close($curl);

    return ['header' => $header, 'body' => $body];
}

/**
 * Get request protocol.
 * 
 * @return string Protocol.
 */
function getProtocol() {
	if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){
		return $_SERVER['HTTP_X_FORWARDED_PROTO'];
	}else{
		$protocol = 'http';
		if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on"){
			$protocol .= 's';
		}
		return $protocol;
	}
}

/**
 * Get request port.
 * 
 * @return int Request port number.
 */
function getPort() {
	$port = (int)(
		isset($_SERVER["HTTP_X_FORWARDED_PORT"])
			? $_SERVER["HTTP_X_FORWARDED_PORT"]
			: (isset($_SERVER['SERVER_PORT'])
				? $_SERVER['SERVER_PORT']
				: 80
			)
	);
	return $port;
}

/**
 * Get request client IP address.
 * 
 * @return string Client IP address.
 */
function getIp() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif (!empty($_SERVER['REMOTE_ADDR'])) {
		return $_SERVER['REMOTE_ADDR'];
	}
}

/**
 * Get user agent.
 * 
 * @return string Http client user agent.
 */
function getUserAgent() {
	return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
}

/**
 * Get IP addess type.
 * 
 * @param string $ip IP address.
 * 
 * @return Array IP address type, false if undetected.
 */
function ipType($ip) {
	if (strpos($ip, ":") !== false && strpos($ip, ".") === false)
		return [0, "v6-pure"];
	else if (strpos($ip, ":") !== false && strpos(explode(":", $ip)[sizeof(explode(":", $ip)) - 1], ".") !== false)
		return [1, "v6-dual"];
	else if (strpos($ip, ":") === false && strpos($ip, ".") !== false)
		return [2, "v4"];
	else
		return false;
}

/**
 * Get server name, domain.
 * 
 * @return string Domain name, false if undetectable or using CLI.
 */
function getDomain() {
	if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '') {
		return $_SERVER['HTTP_HOST'];
	}
	else if (isset($_SERVER['SERVER_NAME'])) {
		return $_SERVER['SERVER_NAME'];
	}
	return null;
}

/**
 * Get base url.
 * change: use getProtocol() method, for DRY purposes.
 * 
 * @return string Base URL.
 */
function getBaseUrl() {
	$serverName = rtrim(getDomain() . BASE_URL, '/');
	return sprintf("%s://%s", isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $serverName);
}

/**
 * Get http basic authentication username and password.
 * 
 * @return Array Username and password.
 */
function getHttpBasicAuth() {
	if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
		return [$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']];
	}
	else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
		list($username, $password) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		return [$username, $password];
	}
	else {
		return [null, null];
	}
}

/**
 * Validate http basic authentication request.
 * 
 * @param $credentials Array Valid credentials.
 * @param string $realm Optional. Page realm.
 * @param string $output Optional. Failure message.
 * 
 * @return mixed Authenticated user credential.
 */
function validateHttpBasicAuth($credentials, $realm = null, $output = null) {
	$realm = $realm ? $realm : 'Please enter your username and password to proceed further';
	$output = $output ? $output : 'Oops! It require login to proceed further. Please enter your login detail';
	$restricted = function() use ($realm, $output) {
		header("WWW-Authenticate: Basic realm=\"{$realm}\"");
		header("HTTP/1.0 401 Unauthorized");
		if ($output instanceof \Closure) {
			$output();
		}
		else {
			echo $output;
		}
		exit;
	};
	list($authUser, $authPw) = getHttpBasicAuth();
	if (!$authUser && !$authPw) {
		$restricted();
	}
	if (!is_array($credentials[0])) {
		$credentials = ['', $credentials];
	}
	$authenticated = null;
	foreach ($credentials as $credential) {
		if ($credential[0] == $authUser && $credential[1] == $authPw) {
			$authenticated = $credential;
		}
	}
	if (!$authenticated) {
		$restricted();
	}
	return $authenticated;
}

/**
 * ==================================================
 * Strings
 * ==================================================
 */

/**
 * Interpolate variables into a string.
 * 
 * @param string $string The string template.
 * @param Array $context Data to interpolate.
 * @param Array $keyWrap Data key wrap.
 * 
 * @return string The interpolated string.
 */
function interpolate($string, array $context = array(), array $keyWrap = array('{', '}')) {
	$keyWrapSize = sizeof($keyWrap);
	if ($keyWrapSize < 2) {
		if ($keyWrapSize == 1) {
			$keyWrap = array($keyWrap[0], $keyWrap[0]);
		}
		else {
			$keyWrap = array('{', '}');
		}
	}
	$replace = array();
	foreach ($context as $key => $val) {
		if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
			$replace["{$keyWrap[0]}{$key}{$keyWrap[1]}"] = $val;
		}
	}
	return strtr($string, $replace);
}

/**
 * Splits a series of queries from a string by semi-colons
 * Returns array of queries, with or without the semi-colon at the end
 * change: if $keepSemiColon is false just trim queries string.
 * 
 * @source http://stackoverflow.com/questions/24423260/split-sql-statements-in-php-on-semicolons-but-not-inside-quotes
 * 
 * @param string $queries Queries string.
 * @param bool $keepSemiColon Optional. Keep last semi-colon. Resulting array would have an empty last index.
 * 
 * @return Array Split queries.
 */
function splitQueries($queries, $keepSemiColon = false) {
	if ($keepSemiColon) {
		return preg_split('~\([^)]*\)(*SKIP)(*F)|(?<=;)(?![ ]*$)~', $queries);
	}
	else {
		$queries = preg_split('~\([^)]*\)(*SKIP)(*F)|;~', $queries);
		$lastIndex = sizeof($queries) - 1;
		if (!$queries[$lastIndex] || $queries[$lastIndex] == '') {
			array_pop($queries);
		}
		return $queries;
	}
}

/**
 * Strip string of non alpha-numeric characters and underscore. Also keep dashes by default.
 * 
 * @param string $s String to strip.
 * @param bool $dash Optional. Keep dashes.
 * 
 * @return string Stripped string.
 */
function stripString($s, $dash = true) {
	if ($dash)
		return preg_replace('/[^\w-]/', '', $s);
	else
		return preg_replace("/[^\w]+/", '', $s);
}

/**
 * Check if string is serialized data.
 * 
 * @param string $string The string to check.
 * 
 * @return bool True if string is serialized data, false otherwise.
 */
function isSerialized($string) {
	return ($string == serialize(false) || @unserialize($string) !== false);
}

/**
 * Check if string starts with specific value.
 * 
 * @param string $haystack String to check.
 * @param string $needle String to check for.
 * 
 * @return bool True if string starts with value, false otherwise.
 */
function startsWith($haystack, $needle) {
	return preg_match('/^' . preg_quote($needle, '/') . '/', $haystack);
	// return $needle === "" || strpos($haystack, $needle) !== FALSE && strpos($haystack, $needle) === 0;
}

/**
 * Check if string ends with specific value.
 * 
 * @param string $haystack String to check.
 * @param string $needle String to check for.
 * 
 * @return bool True if string ends with value, false otherwise.
 */
function endsWith($haystack, $needle) {
	return preg_match('/' . preg_quote($needle, '/') . '$/', $haystack);
	// if ($haystack == '') return false;
	// return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
}

/**
 * Write data into a single line in CSV file format.
 * 
 * @param Array Data to write.
 * 
 * @return string Line of data in CSV format.
 */
function csvLine($data) {
	$h = fopen('php://temp', 'r+');
	fputcsv($h, $data, ',', '"');
	rewind($h);
	$data = fread($h, 1048576);
	fclose($h);
	return trim($data);
}

/**
 * Sanitize and remove extra whitespace from HTML. Simple method.
 * Caution, does not handle <code> and <pre> tags.
 * Caution when using inline <script>. Semi-colon (;) is required after every statement
 * and // comments break
 * 
 * @source https://stackoverflow.com/a/6225706
 * 
 * @param string $buffer HTML code to minify.
 * 
 * @return string Minified HTML code.
 */
function sanitizeOutput($buffer) {
	$search = array(
		'/\>[^\S ]+/s',     // strip whitespaces after tags, except space
		'/[^\S ]+\</s',     // strip whitespaces before tags, except space
		'/(\s)+/s',         // shorten multiple whitespace sequences
		'/<!--(.|\s)*?-->/' // Remove HTML comments
	);
	$replace = array(
		'>',
		'<',
		'\\1',
		''
	);
	$buffer = preg_replace($search, $replace, $buffer);
	return $buffer;
}

/**
 * Shorthand for PHP htmlentities with ENT_QUOTES flag and default UTF-8 encoding.
 * 
 * @param string $content String to escape.
 * @param string $encoding Optional. Encoding type.
 * 
 * @return string Escaped content.
 */
function escape($content, $encoding = "UTF-8") {
	return htmlentities($content, ENT_QUOTES, $encoding);
}

/**
 * Clean URL slug and remove unwanted characters.
 * Also remove double hyphens and underscrores.
 * 
 * @param string $slug Slug value to clean.
 * @param bool $lowercase Optional. Whether or not to convert to lowercase.
 * @param string $regex Optional. Regex for removing unwanted characters.
 * 
 * @return string Cleaned slug
 */
function cleanSlug($slug, $lowercase = true, $regex = "/[^a-z0-9-_]/i") {
	$slug = str_replace(' ', '-', $slug);
	$slug = preg_replace($regex, "", iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug));
	$slug = str_replace('--', '-', $slug);
	$slug = str_replace('__', '_', $slug);
	$slug = trim($slug, '-');
	$slug = trim($slug, '_');
	return $lowercase ? strtolower($slug) : $slug;
}

/**
 * Build HTML attributes string from array of values.
 * 
 * @param Array $data Key value pairs.
 * @param string $prefix Optional. Attribute prefix.
 * @param bool $padding Optional. Whether or not to keep leading space.
 * 
 * @return string HTML attribute string.
 */
function attributes($data, $prefix = null, $padding = true) {
	$attrString = '';
	foreach ($data as $k => $v)
		$attrString .= $v !== null ? " {$prefix}{$k}=\"{$v}\"" : " {$prefix}{$k}";
	if (!$padding && $attrString != '')
		$attrString = trim($attrString);
	return $attrString;
}

/**
 * ==================================================
 * Filesystem
 * ==================================================
 */

/**
 * Normalize file path with system directory separator.
 * 
 * @param string $path File path.
 * 
 * @return string File path with normalized directory separators.
 */
function path($path) {
	return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

/**
 * Real relative path, removes `.` and `..` from paths.
 * 
 * @param string $path File path.
 * 
 * @return string Real path.
 */
function realRelPath($path) {
	$final = array();
	foreach (explode('/', $path) as $part) {
		if ($part === '..') {
			array_pop($final);
		}
		else if ($part && $part != '.' && $part != '') {
			$final[] = $part;
		}
	}
	$startingSlash = preg_match('/^\//', $path) ? '/' : '';
	$endingSlash = preg_match('/\/$/', $path) ? '/' : '';
	return $startingSlash . implode('/', $final) . $endingSlash;
}

/**
 * Format file sizes in more readable format with labels.
 * 
 * @param int $filesize File size in bytes.
 * @param Array $labels Optional. Array of size labels. Using abbreviations as keys.
 * 
 * @return string Formatted file size.
 */
function formatFileSize($filesize, $labels = []) {
	$labels = array_merge([
		'b' => 'b'
		,'kb' => 'kb'
		,'mb' => 'mb'
		,'gb' => 'gb'
		,'tb' => 'tb'
	], $labels);
	if ($filesize < 1024)
		$filesize = round($filesize, 2) . $labels['b'];
	else if ($filesize >= 1024 && $filesize < (1024 * 1024))
		$filesize = round($filesize / 1024, 2) . $labels['kb'];
	else if ($filesize >= (1024 * 1024) && $filesize < (1024 * 1024 * 1024))
		$filesize = round($filesize / 1024 / 1024, 2) . $labels['mb'];
	else if ($filesize >= (1024 * 1024 * 1024) && $filesize < (1024 * 1024 * 1024 * 1024))
		$filesize = round($filesize / 1024 / 1024 / 1024, 2) . $labels['gb'];
	else
		$filesize = round($filesize / 1024 / 1024 / 1024  /1024, 2) . $labels['tb'];
	return $filesize;
}

/**
 * Clear/delete files in directory.
 * 
 * @param string $dir Directory path.
 * 
 * @return int Deleted file count.
 */
function clrDir($dir) {
	 // chmod($dir, 0755);
	 $di = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
	 $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
	 $count = 0;
	 foreach ( $ri as $file ) {
		 if ($file->isDir()) {
		 	rmdir($file);
		 	$count++;
		 }
		 else {
		 	unlink($file);
		 	$count++;
		 }
		 $count++;
	 }
	 //rmdir($dir);
	 return $count;
}

/**
 * Get contents of directory.
 * 
 * @param string $dir Directory path.
 * @param Array $results Optional. File list.
 * @param bool $filesOnly Optional. Find files only.
 * @param bool $foldersOnly Optional. Find folders only.
 * @param Array $excludedFiles Optional. Array of files to exclude. Relative to directory.
 * @param Array $excludedFolders Optional. Array of folders to exclude. Relative to directory.
 * 
 * @return Array List of files/folders found.
 */
function getDirContents($dir, &$results = array(), $filesOnly = false, $foldersOnly = false, $excludedFiles = ['.DS_Store'], $excludedFolders = []){
	$files = $dir ? scandir($dir) : Array();
	if (!$excludedFiles || !is_array($excludedFiles)) {
		$excludedFiles = ['.DS_Store'];
	}
	if (!$excludedFolders || !is_array($excludedFolders)) {
		$excludedFolders = [];
	}
	foreach($files as $key => $value){
		$path = realpath($dir.DIRECTORY_SEPARATOR.$value);
		if(!is_dir($path)) {
			$excluded = array_merge(['.', '..'], $excludedFiles);
			if (!in_array(basename($path), $excluded)) {
				if ($filesOnly && !is_dir($path)) {
					$results[] = $path;
				}
				else if (!$filesOnly && !$foldersOnly) {
					$results[] = $path;
				}
			}
		}
		else if (!in_array($value, ['.', '..'])){
			$passed = true;
			foreach ($excludedFolders as $folder) {
				if (preg_match('#' . preg_quote($folder, '#') . '$#', $path)) {
					$passed = false;
				}
			}
			if ($passed) {
				getDirContents($path, $results, $filesOnly, $foldersOnly, $excludedFiles, $excludedFolders);
				if ($foldersOnly && !$filesOnly) {
					$results[] = $path;
				}
				else if (!$foldersOnly && !$filesOnly) {
					$results[] = $path;
				}
			}
		}
	}
	return $results;
}

/**
 * Get file mime type.
 * 
 * @param string $filename File path.
 * 
 * @return string Mime type.
 */
function mimeType($filename) {
	$fileExists = file_exists($filename);
	if(function_exists('mime_content_type') && $mimeType = mime_content_type($filename))
		return $mimeType;
	else if ($fileExists && function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);
		return $mimetype;
	}
	else {
		$filename = escapeshellcmd($filename);
		$command = "file -b --mime-type -m /usr/share/misc/magic {$filename}";
		$mimeType = shell_exec($command);
		return trim($mimeType);
	}
}

/**
 * Get file extension.
 * 
 * @param string $filename File path.
 * 
 * @return string File extension.
 */
function fileExt($filename) {
	$fileinfo = pathinfo($filename);
	return isset($fileinfo['extension']) ? $fileinfo['extension'] : null;
}

/**
 * Verify directory exists, if not create it.
 * 
 * @param string $path Directory path.
 * @param int $permissions Directory permissions.
 */
function verifyDir($path, $permissions = 0755) {
	if (!file_exists($path))
		mkdir($path, $permissions, true);
}

/**
 * Delete symlink.
 * 
 * @param string $linkfile Symlink path.
 */
function deleteSymlink($linkfile) {
	if(file_exists($linkfile)) {
		if(is_link($linkfile))
			unlink($linkfile);
	}
}

/**
 * Delete symlink target file or directory.
 * 
 * @param string $linkfile Symlink path.
 */
function deleteSymlinkTarget($linkfile) {
	if(is_link($linkfile)) {
		$target = readlink($linkfile);
		if (file_exists($target)) {
			if (is_dir($target))
				rmdir($target);
			else
				unlink($target);
		}
	}
}

/**
 * ==================================================
 * Array
 * ==================================================
 */

/**
 * Recursively deep merge arrays.
 * 
 * @param Array Arrays to deep merge. Unlimited arrays can be passed in as individual arguments.
 * 
 * @return Array Single merged array.
 */
function deepMerge() {
	$args = func_get_args();
	if (sizeof($args) == 0) {
		return [];
	}
	$merged = array_shift($args);
	foreach ($args as $arg) {
		if (!is_array($arg)) {
			$merged[] = $arg;
			// ? add to array or ignore
			// throw new \Exception('not array');
			// die('not array');
		}
		else {
			foreach ($arg as $key => &$value) {
				if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
					$merged[$key] = deepMerge($merged[$key], $value);
				}
				else {
					$merged[$key] = $value;
				}
			}
		}
	}
	return $merged;
}

/**
 * Recursively deep merge 2 arrays. Faster for large arrays and less memory usage.
 * 
 * @param Array $array1 First array to deep merge.
 * @param Array $array2 Second array to deep merge.
 * 
 * @return Array Single merged array.
 */
function deepMergeRef(array &$array1, array &$array2) {
	foreach ($array2 as $key => &$value) {
		if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
			$array1[$key] = deepMerge($array1[$key], $value);
		}
		else {
			$array1[$key] = $value;
		}
	}
	return $array1;
}

/**
 * Check if array is associative or not.
 * 
 * @param Array $a Array to check.
 * 
 * @return bool True if array is associative, false otherwise.
 */
function isAssoc(array $a) {
	foreach (array_keys($a) as $k)
		if (!is_int($k)) return true;
	return false;
}

/**
 * ==================================================
 * Datetime
 * ==================================================
 */

/**
 * Generate add datetime string, for use with DateInterval.
 * 
 * @param Array $toAdd Date and time values.
 * 
 * @return string Datetime add string.
 */
function addDTS($toAdd) {
	$toAdd = array_merge(['y' => 0, 'm' => 0, 'd' => 0, 'h' => 0, 'i' => 0, 's' => 0], $toAdd);
	$units = array('y','m','d','h','i','s');
	$addString = 'P{@y}Y{@m}M{@d}DT{@h}H{@i}M{@s}S';
	foreach ($toAdd as $key => $val) {
		if ($val === null || trim($val) == '')
			$val = 0;
		$addString = str_replace("{@" . $key . "}", $val, $addString);
	}
	return $addString;
}

/**
 * Get available timezones.
 * 
 * @return Array List of timezones.
 */
function getTimezones() {
    $timezoneIdentifiers = DateTimeZone::listIdentifiers();
    $utcTime = new DateTime('now', new DateTimeZone('UTC'));
    $tempTimezones = array();
    foreach ($timezoneIdentifiers as $timezoneIdentifier) {
        $currentTimezone = new DateTimeZone($timezoneIdentifier);
        $tempTimezones[] = array(
            'offset' => (int)$currentTimezone->getOffset($utcTime),
            'identifier' => $timezoneIdentifier
        );
    }
    // Sort the array by offset,identifier ascending
    usort($tempTimezones, function($a, $b) {
        return ($a['offset'] == $b['offset'])
            ? strcmp($a['identifier'], $b['identifier'])
            : $a['offset'] - $b['offset'];
    });
    $timezoneList = array();
    foreach ($tempTimezones as $tz) {
        $sign = ($tz['offset'] > 0) ? '+' : '-';
        $offset = gmdate('H:i', abs($tz['offset']));
        $timezoneList[$tz['identifier']] = $sign . $offset;
    }
    return $timezoneList;
}

function toDateTime($value, $format = null) {
	$object = null;
	if ($value) {
		try {
			if ($format) {
				$object = DateTime::createFromFormat($format, $value);
			}
			else {
				$formats = [
					'/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9])$/' => 'Y-m-d'
					,'/^(?:[0-2][0-9]):(?:[0-5][0-9])$/' => 'H:i'
					,'/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9]) (?:[0-2][0-9]):(?:[0-5][0-9])$/' => 'Y-m-d H:i'
					,'/^\d\d\d\d-(?:[0-1][0-9])-(?:[0-3][0-9]) (?:[0-1]?[0-9]):(?:[0-5][0-9]) (am|pm|AM|PM)$/' => 'Y-m-d g:i a'
					,'/^\d{14}$/' => 'YmdHis'
					,'/^\d{12}$/' => 'YmdHi'
					,'/^\d{8}$/' => 'Ymd'
					,'/^\d{4}$/' => 'Hi'
				];
				foreach ($formats as $regex => $format) {
					if (preg_match($regex, $value)) {
						$object = DateTime::createFromFormat($format, strtolower($value));
					}
				}
				if (!$object) {
					$object = new DateTime($value);
				}
			}
		}
		catch (Exception $e) {
			$object = null;
		}
	}
	return $object ?: null;
}

/**
 * ==================================================
 * Cryptography and Authentication
 * ==================================================
 */

/**
 * Hash value with optional salt.
 * 
 * @param string $value Value to hash.
 * @param string $salt Optional. Hash salt value.
 * @param string $type Optional. Hash algorithm.
 * 
 * @return string Hashed value.
 */
function hashValue($value, $salt='', $type='sha1') {
	if (sizeof(explode('.', $salt)) == 2)
		return hash($type, $salt.$value.explode('.', $salt)[1].$value.explode('.', $salt)[0]);
	else
		return hash($type, $salt.$value.$salt.$value.$salt);
}

/**
 * Create hashed password string.
 * 
 * @param string $password Password to hash.
 * @param string $algo Hash algorithm.
 * 
 * @return string Hashed password string.
 */
function createPassword($password, $algo = 'sha1') {
	$salt = getUniqueId();
	$passHash = hashValue($password, $salt, $algo);
	return "{$algo}\${$salt}\${$passHash}";
}

/**
 * Verify password against hashed version.
 * 
 * @param string $password Unhashed password.
 * @param string $passString Hashed password string.
 * 
 * @return bool True if password is valid, false otherwise.
 */
function verifyPassword($password, $passString) {
	$currentPasswordHash = $currentPasswordSalt = $currentPasswordAlgorithm = 'sha1$0.0$0';
	$currentPasswordHash = explode('$', $passString);
	$currentPasswordSalt = $currentPasswordHash[1];
	$currentPasswordAlgorithm = $currentPasswordHash[0];
	$currentPasswordHash = $currentPasswordHash[2];
	return trim(hashValue($password, $currentPasswordSalt, $currentPasswordAlgorithm)) == trim($currentPasswordHash);
}

/**
 * Encrypt data, using AES-256-CBC mode.
 * Generates 'key' from the provided password using SHA256.
 * Generates hmac has of the encrypted data for integrity check.
 * Generates random IV for each message.
 * Prepends IV (16b) and the hash (32b) to the ciphertext.
 * 
 * @source https://stackoverflow.com/a/46872528
 * 
 * @param string $plaintext Text/data to encrypt.
 * @param string $password Password to encrypt data with.
 * 
 * @return string Ciphertext with IV and hash appended to it.
 */
function encrypt($plaintext, $password) {
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(16);
    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);
    return $iv . $hash . $ciphertext;
}

/**
 * Decrypt data, using AES-256-CBC mode. Ciphertext from \lynk\encrypt method.
 * 
 * @source https://stackoverflow.com/a/46872528
 * 
 * @param string $ivHashCiphertext IV+Hash+Ciphertext to decrypt.
 * @param string $password Password to decrypt data with.
 * 
 * @return string Decrypted data.
 */
function decrypt($ivHashCiphertext, $password) {
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $password, true);
    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;
    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}

/**
 * ==================================================
 * Colors
 * ==================================================
 */

/**
 * Convert rgb color values to hex.
 * 
 * @param Array $rgb Rgb colors.
 * 
 * @return string Hex color value.
 */
function rgbToHex($rgb) {
	//--rgba regex
	//--matches ###(1),###(2),###(3),#.#(4)?
	//--/rgb(?:a)?\( ?([\d]{1,3}) ?, ?([\d]{1,3}) ?, ?([\d]{1,3}) ?,? ?([\d](?:\.[\d])?)? ?\)/
	$rgb = array_values($rgb);
	return '#' . sprintf('%02x', $rgb[0]) . sprintf('%02x', $rgb[1]) . sprintf('%02x', $rgb[2]);
}

/**
 * Convert hex color to rgb values.
 * 
 * @param string $hex Hex color value.
 * @param bool $assoc Return associative array.
 * 
 * @return Array Rgb values.
 */
function hexToRgb($hex, $assoc = false) {
	$hex = str_replace("#", "", $hex);
	$r = $g = $b = null;
	if ($hex && preg_match('/^[0-9a-f]+$/i', $hex)) {
		if(strlen($hex) == 3) {
			$r = hexdec($hex[0].$hex[0]);
			$g = hexdec($hex[1].$hex[1]);
			$b = hexdec($hex[2].$hex[2]);
		} else if (strlen($hex) == 6) {
			$r = hexdec($hex[0].$hex[1]);
			$g = hexdec($hex[2].$hex[3]);
			$b = hexdec($hex[4].$hex[5]);
		}
	}
	if ($assoc)
		return ['r' => $r, 'g' => $g, 'b' => $b];
	else
		return [$r, $g, $b];
}

/**
 * Generate a random color, returns both RGB and HEX version.
 * 
 * @param int $lower Optional. Lower color value, 0-255.
 * @param int $upper Optional. Upper color value, 0-255.
 */
function randomColor($lower = 0, $upper = 255) {
	$c1 = str_pad(dechex(mt_rand($lower, $upper)), 2, '0', STR_PAD_LEFT);
	$c2 = str_pad(dechex(mt_rand($lower, $upper)), 2, '0', STR_PAD_LEFT);
	$c3 = str_pad(dechex(mt_rand($lower, $upper)), 2, '0', STR_PAD_LEFT);
	return Array("#{$c1}{$c2}{$c3}", \lynk\hexToRgb("#{$c1}{$c2}{$c3}", true));
}

/**
 * ==================================================
 * Other
 * ==================================================
 */

/**
 * Returns used memory (either in percent (without percent sign) or free and overall in bytes).
 * 
 * @source https://www.php.net/manual/en/function.memory-get-peak-usage.php
 * 
 * @param mixed Array of total and free memory or precentage. Null if not available or failure.
 */
function getServerMemoryUsage($getPercentage=true) {
    $memoryTotal = null;
    $memoryFree = null;

    if (stristr(PHP_OS, "win")) {
        // Get total physical memory (this is in bytes)
        $cmd = "wmic ComputerSystem get TotalPhysicalMemory";
        @exec($cmd, $outputTotalPhysicalMemory);

        // Get free physical memory (this is in kibibytes!)
        $cmd = "wmic OS get FreePhysicalMemory";
        @exec($cmd, $outputFreePhysicalMemory);

        if ($outputTotalPhysicalMemory && $outputFreePhysicalMemory) {
            // Find total value
            foreach ($outputTotalPhysicalMemory as $line) {
                if ($line && preg_match("/^[0-9]+\$/", $line)) {
                    $memoryTotal = $line;
                    break;
                }
            }

            // Find free value
            foreach ($outputFreePhysicalMemory as $line) {
                if ($line && preg_match("/^[0-9]+\$/", $line)) {
                    $memoryFree = $line;
                    $memoryFree *= 1024;  // convert from kibibytes to bytes
                    break;
                }
            }
        }
    }
    else
    {
        if (is_readable("/proc/meminfo"))
        {
            $stats = @file_get_contents("/proc/meminfo");

            if ($stats !== false) {
                // Separate lines
                $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
                $stats = explode("\n", $stats);

                // Separate values and find correct lines for total and free mem
                foreach ($stats as $statLine) {
                    $statLineData = explode(":", trim($statLine));

                    //
                    // Extract size (TODO: It seems that (at least) the two values for total and free memory have the unit "kB" always. Is this correct?
                    //

                    // Total memory
                    if (count($statLineData) == 2 && trim($statLineData[0]) == "MemTotal") {
                        $memoryTotal = trim($statLineData[1]);
                        $memoryTotal = explode(" ", $memoryTotal);
                        $memoryTotal = $memoryTotal[0];
                        $memoryTotal *= 1024;  // convert from kibibytes to bytes
                    }

                    // Free memory
                    if (count($statLineData) == 2 && trim($statLineData[0]) == "MemFree") {
                        $memoryFree = trim($statLineData[1]);
                        $memoryFree = explode(" ", $memoryFree);
                        $memoryFree = $memoryFree[0];
                        $memoryFree *= 1024;  // convert from kibibytes to bytes
                    }
                }
            }
        }
    }

    if (is_null($memoryTotal) || is_null($memoryFree)) {
        return null;
    } else {
        if ($getPercentage) {
            return (100 - ($memoryFree * 100 / $memoryTotal));
        } else {
            return array(
                "total" => $memoryTotal,
                "free" => $memoryFree,
            );
        }
    }
}

/**
 * Returns server load in percent (just number, without percent sign).
 * 
 * @source https://www.php.net/manual/en/function.sys-getloadavg.php#118673
 * 
 * @return float Server load in percent.
 */
function getServerLoad() {
    $load = null;

    if (stristr(PHP_OS, "win"))
    {
        $cmd = "wmic cpu get loadpercentage /all";
        @exec($cmd, $output);

        if ($output)
        {
            foreach ($output as $line)
            {
                if ($line && preg_match("/^[0-9]+\$/", $line))
                {
                    $load = $line;
                    break;
                }
            }
        }
    }
    else
    {
        if (is_readable("/proc/stat"))
        {
            // Collect 2 samples - each with 1 second period
            // See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
            $statData1 = \lynk\_getServerLoadLinuxData();
            sleep(1);
            $statData2 = \lynk\_getServerLoadLinuxData();

            if
            (
                (!is_null($statData1)) &&
                (!is_null($statData2))
            )
            {
                // Get difference
                $statData2[0] -= $statData1[0];
                $statData2[1] -= $statData1[1];
                $statData2[2] -= $statData1[2];
                $statData2[3] -= $statData1[3];

                // Sum up the 4 values for User, Nice, System and Idle and calculate
                // the percentage of idle time (which is part of the 4 values!)
                $cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];

                // Invert percentage to get CPU time, not idle time
                $load = 100 - ($statData2[3] * 100 / $cpuTime);
            }
        }
    }

    return $load;
}

/**
 * Returns server load values.
 * 
 * @return Array Server loads.
 */
function _getServerLoadLinuxData() {
    if (is_readable("/proc/stat"))
    {
        $stats = @file_get_contents("/proc/stat");

        if ($stats !== false)
        {
            // Remove double spaces to make it easier to extract values with explode()
            $stats = preg_replace("/[[:blank:]]+/", " ", $stats);

            // Separate lines
            $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
            $stats = explode("\n", $stats);

            // Separate values and find line for main CPU load
            foreach ($stats as $statLine)
            {
                $statLineData = explode(" ", trim($statLine));

                // Found!
                if
                (
                    (count($statLineData) >= 5) &&
                    ($statLineData[0] == "cpu")
                )
                {
                    return array(
                        $statLineData[1],
                        $statLineData[2],
                        $statLineData[3],
                        $statLineData[4],
                    );
                }
            }
        }
    }

    return null;
}

/**
 * Check if envrionment is command line interface or not.
 * 
 * @return bool True if CLI, false otherwise.
 */
function isCli() {
	return (php_sapi_name() == "cli");
}

/**
 * Build a hierarchy tree select element options. Uses '-' for nested options.
 * 
 * @param Array $selectData Empty array where resulting select element options are stored.
 * @param Array $data Source data to build options from. Multi-dimensional array with child arrays having 3 elements.
 *                    - uid: id of the item
 *                    - name: item name, for output
 *                    - children: child items, optional.
 * @param string $pad Optional. Child element padding. Gets mutiplied by depth level, default is '-'.
 * @param int $depth Optional. Depth level.
 */
function buildHierarchyTreeSelectOptions(&$selectData, $data, $pad = null, $depth = -1) {
	$pad = $pad ?: '&#x2500;';
	$depth++;
	$depthStr = str_repeat($pad, $depth);
	if ($depthStr != '')
		$depthStr = "{$depthStr} ";
	foreach ($data as $key => $data) {
		if (!isset($data['children'])) {
			$data['children'] = [];
		}
		if (sizeof($data['children']) == 0) {
			$selectData["id_{$data['uid']}"] = "{$depthStr}{$data['uid']}: {$data['name']}";
		}
		else {
			$selectData["id_{$data['uid']}"] = "{$depthStr}{$data['uid']}: {$data['name']}";
			buildHierarchyTreeSelectOptions($selectData, $data['children'], $pad, $depth);
		}
	}
}

/**
 * Build hierarchy tree using set of data.
 * change: make excluded option and array so more than one item can be excluded.
 * 
 * @param Array $tmpGroups Initial source data.
 * @param mixed $parentId Optional. Parent item id.
 * @param mixed $excluded Optional. Excluded item by id, also excludes resulting children.
 * @param Array $keys Optional. Define id and name keys for dataset.
 * 
 * @return Array Built Hierarchy tree data.
 */
function buildHierarchyTree($tmpGroups, $parentId = null, $excluded = null, $keys = []) {
	$keys = array_merge(['id' => 'id', 'parent' => 'parent_id'], $keys);
	$idKey = $keys['id'];
	$parentKey = $keys['parent'];
	$groups = [];
	$tmpGroups = array_values($tmpGroups);
	foreach ($tmpGroups as $group) {
		$group['children'] = [];
		$groups["id_{$group[$idKey]}"] = $group;
	}
	$getUserGroupTree = function($groups, $parentId, $excluded, $keys) use (&$getUserGroupTree, $idKey, $parentKey) {
		$data = [];
		foreach ($groups as $groupKey => $groupValue) {
			if ($groupValue[$parentKey] == $parentId && $groupValue[$idKey] != $excluded) {
				$data["id_{$groupValue[$idKey]}"] = $groupValue;
				$data["id_{$groupValue[$idKey]}"]['children'] = $getUserGroupTree($groups, $groupValue[$idKey], $excluded, $keys);
			}
		}
		return $data;
	};
	if ($parentId && $parentId != $excluded) {
		if (isset($groups["id_{$parentId}"])) {
			$groups["id_{$parentId}"]['children'] = $getUserGroupTree($groups, $groups["id_{$parentId}"][$idKey], $excluded, $keys);
			return ["id_{$parentId}" => $groups["id_{$parentId}"]];
		}
	}
	else {
		foreach ($groups as $key => &$group) {
			if ((!$group[$parentKey] || $group[$parentKey] == '') && $group[$idKey] != $excluded) {
				$group['children'] = $getUserGroupTree($groups, $group[$idKey], $excluded, $keys);
			}
		}
		foreach ($groups as $key => &$group) {
			if ($group[$parentKey] || $group[$idKey] == $excluded) {
				unset($groups[$key]);
			}
		}
	}
	return $groups;
}

/**
 * Get pagination for use in building HTML pagination widgets.
 * 
 * @param int $current Current page.
 * @param int $last Last available page.
 * @param int $delta Optional. Pages to show on left and right of current page.
 * @param int $start Optional. Pages to show at the start of the pagination list.
 * @param int $end Optional. Pages to show at the end of the pagination list.
 * 
 * @return Array Pagination data list.
 */
function getPagination($current, $last, $delta = 2, $start = 1, $end = 1) {
	$left = $current - $delta;
	$right = $current + $delta + 1;
	$range = $rangeWithDots = [];
	$l = -1;
	for ($i = 1; $i <= $last; $i++) {
		$startTest = ($i <= $start);
		$endTest = ($i >= ($last - ($end - 1)));
		$rangeTest = ($i >= $left && $i < $right);
		if ($startTest || $endTest || $rangeTest) {
			$range[] = $i;
		}
	}
	for ($i = 0; $i < sizeof($range); $i++) {
		if ($l != -1) {
			if ($range[$i] - $l === 2) {
				$rangeWithDots[] = $i + 1;
			}
			else if ($range[$i] - $l !== 1) {
				$rangeWithDots[] = '...';
			}
		}
		$rangeWithDots[] = $range[$i];
		$l = $range[$i];
	}
	return $rangeWithDots;
}

/**
 * ==================================================
 * Backwards Compatibility - To be removed
 * ==================================================
 */

/**
 * Get uniqid with option for more entropy.
 *
 * @param bool $moreEntropy More entropy.
 * 
 * @return string Unique id.
 */
function getUniqueId($opt=false) {
	return \LynkCMS\Component\Util\UUID::uniqid($opt);
}

/**
 * Get a series of random bytes, 2 cryptographically secure options with a thrid fallback to a non-cryptographically secure option.
 *
 * @param int $length requested byte count.
 * 
 * @return string Series of random bytes as string.
 */
function getRandomBytes($length = 64) {
	return \LynkCMS\Component\Util\UUID::randomBytes($length);
}

/**
 * Generate a randomly unique reference id.
 *
 * @param string $format Optional. Format for reference id. Use '%s' for character placement.
 * @param bool $ramdomizedFormat Optional. Use a random format out the built in choices.
 * 
 * @return string Randomly generated reference id.
 */
function getReferenceId($format = null, $randomizedFormat = true) {
	return \LynkCMS\Component\Util\UUID::referenceId($format, $randomizedFormat);
}
