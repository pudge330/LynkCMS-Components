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
 * @subpackage Util
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Util;

/**
 * Class to generate version 3, 4 and 5 UUIDS and verfiy them.
 * Also can generate random bytes, unique ids and reference ids.
 * UUID functions: http://www.php.net/manual/en/function.uniqid.php#94959
 */
class UUID {

	/**
	 * @var bool Function random_bytes support.
	 */
	protected static $func_random_bytes = false;

	/**
	 * @var bool Function openssl_random_pseudo_bytes support.
	 */
	protected static $func_openssl_random_pseudo_bytes = false;

	/**
	 * Get UUID version 3.
	 *
	 * @param string $namespace A UUID string.
	 * @param string $name A name for the UUID.
	 * 
	 * @return string A version 3 UUID.
	 */
	public static function v3($namespace, $name) {
		if(!self::isValid($namespace)) return false;
		// Get hexadecimal components of namespace
		$nhex = str_replace(array('-','{','}'), '', $namespace);
		// Binary Value
		$nstr = '';
		// Convert Namespace UUID to bits
		for($i = 0; $i < strlen($nhex); $i+=2) {
			$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
		}
		// Calculate hash value
		$hash = md5($nstr . $name);
		return sprintf('%08s-%04s-%04x-%04x-%12s',
			// 32 bits for "time_low"
			substr($hash, 0, 8),
			// 16 bits for "time_mid"
			substr($hash, 8, 4),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 3
			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
			// 48 bits for "node"
			substr($hash, 20, 12)
		);
	}

	/**
	 * Get UUID version 4.
	 *
	 * @return string A version 4 UUID.
	 */
	public static function v4() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	/**
	 * Get UUID version 5.
	 *
	 * @param string $namespace A UUID string.
	 * @param string $name A name for the UUID.
	 * 
	 * @return string A version 5 UUID.
	 */
	public static function v5($namespace, $name) {
		if(!self::isValid($namespace)) return false;
		// Get hexadecimal components of namespace
		$nhex = str_replace(array('-','{','}'), '', $namespace);
		// Binary Value
		$nstr = '';
		// Convert Namespace UUID to bits
		for($i = 0; $i < strlen($nhex); $i+=2) {
			$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
		}
		// Calculate hash value
		$hash = sha1($nstr . $name);
		return sprintf('%08s-%04s-%04x-%04x-%12s',
			// 32 bits for "time_low"
			substr($hash, 0, 8),
			// 16 bits for "time_mid"
			substr($hash, 8, 4),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 5
			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
			// 48 bits for "node"
			substr($hash, 20, 12)
		);
	}

	/**
	 * Validate a UUID version 3-5.
	 *
	 * @param string $uuid A UUID string.
	 * 
	 * @return bool True if valid UUID or false if not.
	 */
	public static function isValid($uuid) {
		return preg_match(
			'/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'
			.'[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i'
			, $uuid
		) === 1;
	}

	/**
	 * Get a series of random bytes, 2 cryptographically secure options with a thrid fallback to a non-cryptographically secure option.
	 *
	 * @param int $length requested byte count.
	 * 
	 * @return string Series of random bytes as string.
	 */
	public static function randomBytes($length = 64) {
		if (self::$func_random_bytes || function_exists("random_bytes")) {
			if (!self::$func_random_bytes)
				self::$func_random_bytes = true;
			return substr(bin2hex(random_bytes(ceil($length / 2))), 0, $length);
		}
		else if (self::$func_openssl_random_pseudo_bytes || function_exists('openssl_random_pseudo_bytes')) {
			if (!self::$func_openssl_random_pseudo_bytes)
				self::$func_openssl_random_pseudo_bytes = true;
			return substr(bin2hex(openssl_random_pseudo_bytes(ceil($length / 2))), 0, $length);
		}
		else {
			for ($i = ''; strlen($i) <= $length;)
				$i .= sha1(self::uniqid(true));
			return substr($i, 0, $length);
		}
	}

	/**
	 * Get uniqid with option for more entropy.
	 *
	 * @param bool $moreEntropy More entropy.
	 * 
	 * @return string Unique id.
	 */
	public static function uniqid($moreEntropy=false) {
		if ($moreEntropy) {
			$itr = rand(10, 100);
			$r = null;
			for ($i = 0; $i < $itr; $i++) { $r = rand(); }
			$uuidGen = uniqid($r, true);
		}
		else {
			$uuidGen = uniqid('', true);
		}
		return substr(sha1(microtime() . rand() . $uuidGen), 0, 16) .
			   ($moreEntropy ? '.' . substr(md5($uuidGen), 0, 7) : '');
	}

	/**
	 * Generate a randomly unique reference id.
	 *
	 * @param string $format Optional. Format for reference id. Use '%s' for character placement.
	 * @param bool $ramdomizedFormat Optional. Use a random format out the built in choices.
	 * 
	 * @return string Randomly generated reference id.
	 */
	public static function referenceId($format = null, $randomizedFormat = true) {
		if (!$format) {
			if ($randomizedFormat) {
				$r = rand(0, 4);
				$format = Array(
					// 10-5-7-7
					"%s%s%s%s%s%s%s%s%s%s-%s%s%s%s%s-%s%s%s%s%s%s%s-%s%s%s%s%s%s%s"
					// 5-7-7-10
					,"%s%s%s%s%s-%s%s%s%s%s%s%s-%s%s%s%s%s%s%s-%s%s%s%s%s%s%s%s%s%s"
					// 10-12-8
					,"%s%s%s%s%s%s%s%s%s%s-%s%s%s%s%s%s%s%s%s%s%s%s-%s%s%s%s%s%s%s%s"
					// 8-7-7-7
					,"%s%s%s%s%s%s%s%s-%s%s%s%s%s%s%s-%s%s%s%s%s%s%s-%s%s%s%s%s%s%s"
					// 8-7-15
					,"%s%s%s%s%s%s%s%s-%s%s%s%s%s%s%s-%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s"
				)[$r];
			}
			else
				// 10-5-7-7
				$format = "%s%s%s%s%s%s%s%s%s%s-%s%s%s%s%s-%s%s%s%s%s%s%s-%s%s%s%s%s%s%s";
		}
		$randomBytes = str_split(self::randomBytes(strlen($format)));
		$reference = call_user_func_array('sprintf', [$format] + $randomBytes);
		return $reference;
	}
}