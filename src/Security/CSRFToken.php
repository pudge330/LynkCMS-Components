<?php
namespace BGStudios\Component\Security;

class CSRFToken {
	private $secretKey;
	public function __construct($skey) {
		$this->secretKey = $skey;
	}
	public function generate($prefix = null, $hash = 'sha256') {
		$hash = in_array($hash, ['md5', 'sha1', 'sha256', 'sha512']) ? $hash : 'sha256';
		if (!$prefix)
			$prefix = microtime(true);
		return \bgs\hashValue($prefix . $this->secretKey . \bgs\getRandomBytes(64), \bgs\getUniqueId(), $hash);
	}
}