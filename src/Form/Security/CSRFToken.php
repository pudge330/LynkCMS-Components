<?php
namespace BGStudios\Component\Form\Security;

class CSRFToken {
	private $secretKey;
	public function __construct($skey) {
		$this->secretKey = $skey;
	}
	public function generate($prefix = null, $hash = 'sha256') {
		$hash = in_array($hash, ['md5', 'sha1', 'sha256', 'sha512']) ? $hash : 'sha256';
		if (!$prefix)
			$prefix = microtime(true);
		$token = hash($hash, $prefix . $this->secretKey . self::getRandomBytes(128));
		return $token;
	}
	public static function getRandomBytes($length) {
		if (function_exists('openssl_random_pseudo_bytes'))
			return substr(bin2hex(openssl_random_pseudo_bytes(ceil($length / 2))), 0, $length);
		else {
			for ($i = ''; strlen($i) <= $length;)
				$i .= str_replace('.', '', md5(self::getUniqueId(true)));
			return substr($i, 0, $length);
		}
	}
	public static function getUniqueId($opt = true) {
		($opt) ? $uuidGen = uniqid(rand(), $opt) : $uuidGen = uniqid(rand());
		return $uuidGen;
	}
}