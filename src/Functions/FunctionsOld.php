<?php
namespace bgs;

class FunctionsOld {}

function curlRequest($url, $options = [], $data = []) {
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

function getUserAgent() {
	return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
}

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

function getDomain() {
	if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != '') {
		return $_SERVER['HTTP_HOST'];
	}
	else if (isset($_SERVER['SERVER_NAME'])) {
		return $_SERVER['SERVER_NAME'];
	}
	return null;
}

function getBaseUrl() {
	$serverName = rtrim(getDomain() . BASE_URL, '/');
	return sprintf("%s://%s", isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $serverName);
}

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
 *	Splits a series of queries from a string by semi-colons
 * 	Returns array of queries, with or without the semi-colon at the end
 *
 *	@source http://stackoverflow.com/questions/24423260/split-sql-statements-in-php-on-semicolons-but-not-inside-quotes
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

function path($path) {
	return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

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

function stripString($s, $dash = true) {
	if ($dash)
		return preg_replace('/[^\w-]/', '', $s);
	else
		return preg_replace("/[^\w]+/", '', $s);
}

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
		$credentials = [$credentials];
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

function getUniqueId($opt=true) { //--bc
	return \BGStudios\Component\UUID\UUID::uniqid($opt);
}

function getRandomBytes($length) { //--bc
	return \BGStudios\Component\UUID\UUID::randomBytes($length);
}

function getReferenceId($format = null) { //--bc
	return \BGStudios\Component\UUID\UUID::referenceId($format);
}

function isSerialized($string) {
	return ($string == serialize(false) || @unserialize($string) !== false);
}

function isAssoc(array $a) {
	foreach (array_keys($a) as $k)
		if (!is_int($k)) return true;
	return false;
}

function startsWith($haystack, $needle) {
	return $needle === "" || strpos($haystack, $needle) !== FALSE && strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle) {
	if ($haystack == '') return false;
	return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
}

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

function hashValue($value, $salt='', $type='sha1') {
	if (sizeof(explode('.', $salt)) == 2)
		return hash($type, $salt.$value.explode('.', $salt)[1].$value.explode('.', $salt)[0]);
	else
		return hash($type, $salt.$value.$salt.$value.$salt);
}

function createPassword($password, $algo = 'sha1') {
	$salt = getUniqueId();
	$passHash = hashValue($password, $salt, $algo);
	return "{$algo}\${$salt}\${$passHash}";
}

function verifyPassword($password, $passString) {
	$currentPasswordHash = $currentPasswordSalt = $currentPasswordAlgorithm = 'sha1$0.0$0';
	$currentPasswordHash = explode('$', $passString);
	$currentPasswordSalt = $currentPasswordHash[1];
	$currentPasswordAlgorithm = $currentPasswordHash[0];
	$currentPasswordHash = $currentPasswordHash[2];
	return trim(hashValue($password, $currentPasswordSalt, $currentPasswordAlgorithm)) == trim($currentPasswordHash);
}

function rgbToHex($rgb) {
	//--rgba regex
	//--matches ###(1),###(2),###(3),#.#(4)?
	//--/rgb(?:a)?\( ?([\d]{1,3}) ?, ?([\d]{1,3}) ?, ?([\d]{1,3}) ?,? ?([\d](?:\.[\d])?)? ?\)/
	$rgb = array_values($rgb);
	return '#' . sprintf('%02x', $rgb[0]) . sprintf('%02x', $rgb[1]) . sprintf('%02x', $rgb[2]);
}

function hexToRgb($hex, $assoc = false) {
	$hex = str_replace("#", "", $hex);
	$r = $g = $b = null;
	if ($hex) {
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
				if (preg_match('#' . preg_quote($folder) . '$#', $path)) {
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

function mimeType($filename) {
	$mimeType = mime_content_type($filename);
	if ($mimeType === false) {
		$filename = escapeshellcmd($filename);
		$command = "file -b --mime-type -m /usr/share/misc/magic {$filename}";
		$mimeType = shell_exec($command);
	}
	if (!$mimeType) {
		$mimeType = null;
	}
	return trim($mimeType);
}

function fileExt($filename) {
	$fileinfo = pathinfo($filename);
	return isset($fileinfo['extension']) ? $fileinfo['extension'] : null;
}

function verifyDir($path) {
	if (!file_exists($path))
		mkdir($path, 0755, true);
}

function deleteSymlink($linkfile) {
	if(file_exists($linkfile)) {
		if(is_link($linkfile))
			unlink($linkfile);
	}
}

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

function buildHierarchyTreeSelectOptions(&$selectData, $data, $pad = null, $depth = -1) {
	$pad = $pad ?: '&#x2500;';
	$depth++;
	$depthStr = str_repeat($pad, $depth);
	if ($depthStr != '')
		$depthStr = "{$depthStr} ";
	foreach ($data as $key => $data) {
		if (sizeof($data['children']) == 0) {
			$selectData["id_{$data['uid']}"] = "{$depthStr}{$data['uid']}: {$data['name']}";
		}
		else {
			$selectData["id_{$data['uid']}"] = "{$depthStr}{$data['uid']}: {$data['name']}";
			buildHierarchyTreeSelectOptions($selectData, $data['children'], $pad, $depth);
		}
	}
}

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

//--@https://stackoverflow.com/a/46872528
function encrypt($plaintext, $password) {
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(16);
    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);
    return $iv . $hash . $ciphertext;
}
function decrypt($ivHashCiphertext, $password) {
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $password, true);
    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;
    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}

// Returns used memory (either in percent (without percent sign) or free and overall in bytes)
// @--https://www.php.net/manual/en/function.memory-get-peak-usage.php
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

// Returns server load in percent (just number, without percent sign)
// @--https://www.php.net/manual/en/function.sys-getloadavg.php#118673
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
            $statData1 = \bgs\_getServerLoadLinuxData();
            sleep(1);
            $statData2 = \bgs\_getServerLoadLinuxData();

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

function isCli() {
	return (php_sapi_name() == "cli");
}

function csvLine($data) {
	$h = fopen('php://temp', 'r+');
	fputcsv($h, $data, ',', '"');
	rewind($h);
	$data = fread($h, 1048576);
	fclose($h);
	return trim($data);
}

function microtime($returnBool = false) {
	
}