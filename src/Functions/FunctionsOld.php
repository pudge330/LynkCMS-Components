<?php
namespace LynkCMS;

class FunctionsOld {}

function getIp() {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getIp();
}

function ipType($ip) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\ipType($ip);
}

function getDomain() {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getDomain();
}

function getBaseUrl() {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getBaseUrl();
}

function interpolate($string, array $context = array(), array $keyWrap = array('{', '}')) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\interpolate($string, $context, $keyWrap);
}

function splitQueries($queries, $keepSemiColon = false) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\splitQueries($queries, $keepSemiColon);
}

function path($path) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\path($path);
}

function realRelPath($path) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\realRelPath($path);
}

function stripString($s, $dash = true) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\stripString($s, $dash);
}

function addDTS($toAdd) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\addDTS($toAdd);
}

function getHttpBasicAuth() {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getHttpBasicAuth();
}

function validateHttpBasicAuth($credentials, $realm = null, $output = null) {
$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\validateHttpBasicAuth($credentials, $realm, $output);
}

function deepMerge() {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return call_user_func_array('\\bgs\\deepMerge', func_get_args());
}

function deepMergeRef(array &$array1, array &$array2) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\deepMergeRef($array1, $array2);
}

function formatFileSize($filesize, $labels = []) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\formatFileSize($filesize, $labels);
}

function getUniqueId($opt=true) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getUniqueId($opt);
}

function getRandomBytes($length) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getRandomBytes($length);
}

function getReferenceId($format = null) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getReferenceId($format);
}

function isSerialized($string) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\isSerialized($string);
}

function isAssoc(array $a) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\isAssoc($a);
}

function startsWith($haystack, $needle) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\startsWith($haystack, $needle);
}

function endsWith($haystack, $needle) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\endsWith($haystack, $needle);
}

function getTimezones() {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getTimezones();
}

function hashValue($value, $salt='', $type='sha1') {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\hashValue($value, $salt, $type);
}

function createPassword($password, $algo = 'sha1') {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\createPassword($password, $algo);
}

function verifyPassword($password, $passString) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\verifyPassword($password, $passString);
}

function rgbToHex($rgb) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\rgbToHex($rgb);
}

function hexToRgb($hex, $assoc = false) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\hexToRgb($hex, $assoc);
}

function clrDir($dir) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\clrDir($dir);
}

function getDirContents($dir, &$results = array(), $exSysFiles = true){
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getDirContents($dir, $results, $exSysFiles);
}

function mimeType($filename = '') {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\mimeType($filename);
}

function verifyDir($path) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\verifyDir($path);
}

function deleteSymlink($linkfile) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\deleteSymlink($linkfile);
}

function deleteSymlinkTarget($linkfile) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\deleteSymlinkTarget($linkfile);
}

function buildHierarchyTreeSelectOptions(&$selectData, $data, $pad = null, $depth = -1) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\buildHierarchyTreeSelectOptions($selectData, $data, $pad, $depth);
}

function buildHierarchyTree($tmpGroups, $parentId = null, $excluded = null, $keys = []) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\buildHierarchyTree($tmpGroups, $parentId, $excluded, $keys);
}

function getPagination($current, $last, $delta = 2, $start = 1, $end = 1) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\getPagination($current, $last, $delta, $start, $end);
}

function encrypt($plaintext, $password) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\encrypt($plaintext, $password);
}
function decrypt($ivHashCiphertext, $password) {
	$stacktrace = \debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
	error_log('Deprecated function \\' . __FUNCTION__ . '() in ' . $stacktrace[0]['file'] . ' on line ' . $stacktrace[0]['line'] . ' use the \\bgs\\ namespace instead');
	return \bgs\decrypt($ivHashCiphertext, $password);
}