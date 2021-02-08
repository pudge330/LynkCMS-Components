<?php
namespace BGStudios\Component\UUID;

/* http://www.php.net/manual/en/function.uniqid.php#94959 */
class UUID {
	protected static $func_random_bytes = false;
	protected static $func_openssl_random_pseudo_bytes = false;
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
	public static function isValid($uuid) {
		return preg_match(
			'/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'
			.'[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i'
			, $uuid
		) === 1;
	}
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
	public static function referenceId($format = null) {
		if ($format)
			$format = str_replace('x', '%s', strtolower($format));
		else
			$format = "%s%s%s%s%s%s%s%s%s%s-%s%s%s%s%s-%s%s%s%s%s%s%s-%s%s%s%s%s%s%s";
		$randomBytes = str_split(self::randomBytes(strlen($format)));
		$reference = call_user_func_array('sprintf', [$format] + $randomBytes);
		return $reference;
	}
}