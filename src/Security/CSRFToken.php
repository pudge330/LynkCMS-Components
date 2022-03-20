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
 * @subpackage Security
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Security;

use Lynk\Component\Util\UUID;

/**
 * CSRF Token class, uses secret key which should be unique to app.
 */
class CSRFToken {

	/**
	 * @var string Secret key.
	 */
	private $secretKey;

	/**
	 * @param string $skey Secret key used for generating tokens.
	 */
	public function __construct($skey) {
		$this->secretKey = $skey;
	}

	/**
	 * Generate a token.
	 * 
	 * @param string $prefix Optional. Prefix for toekn before hashing, if non provided timestamp is used.
	 * @param string $hash Optional. Hash algorithm used to create token.
	 * 
	 * @return string The CSRF token.
	 */
	public function generate($prefix = null, $hash = 'sha256') {
		$hash = in_array($hash, ['md5', 'sha1', 'sha256', 'sha512']) ? $hash : 'sha256';
		if (!$prefix)
			$prefix = microtime(true);
		return \lynk\hashValue($prefix . $this->secretKey . UUID::randomBytes(64), UUID::uniqid(), $hash);
	}
}