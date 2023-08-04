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
 * @subpackage Form
 * @author Brandon Garcia <me@bgarcia.dev>
 */

namespace Lynk\Component\Form2\Security;

use Lynk\Component\Security\CSRFToken;
use Lynk\Component\Util\UUID;

/**
 * Form tokenizer class. Generates and registers token in session.
 */
class FormTokenizer {

	/**
	 * @var string Secret key.
	 */
	protected static $secretKey = null;

	/**
	 * @var CSRFToken Tokenizer class.
	 */
	protected $generator;

	/**
	 * @var string Session token array key.
	 */
	protected $sessionKey;

	/**
	 * @param CSRFToken Optional. Tokenizer class.
	 * @param string Optional. Session token array key.
	 */
	public function __construct($generator = null, $sessionKey = '_formTokens') {
		if (!$generator)
			$generator = static::createToken();
		$this->generator = $generator;
		$this->sessionKey = $sessionKey;
	}

	/**
	 * Set secret key.
	 * 
	 * @param string $secret Server secret key.
	 */
	public static function setSecret($secret) {
		self::$secretKey = $secret;
	}

	/**
	 * Create CSRFToken class instance.
	 * 
	 * @param string $secret Optional. Server secret key.
	 * 
	 * @return CSRFToken Tokenizer instance.
	 */
	public static function createToken($secret = null) {
		if (!$secret)
			$secret = self::$secretKey ?: UUID::randomBytes(128);
		return new CSRFToken($secret);
	}

	/**
	 * Create FormTokenizer class instance.
	 * 
	 * @param string $secret Optional. Server secret key.
	 * 
	 * @return FormTokenizer Form tokenizer instance.
	 */
	public static function create($secret = null) {
		return new FormTokenizer(static::createToken($secret));
	}

	/**
	 * Validate form token with session.
	 * 
	 * @param string $formName Unique form name.
	 * @param string $token Form CSRF token.
	 * @param bool $clear Optional. Remove token from session storage.
	 * 
	 * @return bool True if token is valid, false otherwise.
	 */
	public function validateToken($formName, $token, $clear = true) {
		if (!isset($_SESSION[$this->sessionKey]))
			$_SESSION[$this->sessionKey] = Array();
		if (isset($_SESSION[$this->sessionKey][$formName])) {
			$token = explode(':', $token);
			if ($_SESSION[$this->sessionKey][$formName][$token[0]] != $token[1])
				$pass = false;
			else 
				$pass =  true;
			if ($clear) {
				unset($_SESSION[$this->sessionKey][$formName][$token[0]]);
				if (!sizeof($_SESSION[$this->sessionKey][$formName])) {
					unset($_SESSION[$this->sessionKey][$formName]);
				}
			}
			return $pass;
		}
		else
			return false;
	}

	/**
	 * Register form token in session.
	 * 
	 * @param string $formName Unique form name.
	 * 
	 * @return string Form CSRF token.
	 */
	public function registerToken($formName) {
		if (!isset($_SESSION[$this->sessionKey]))
			$_SESSION[$this->sessionKey] = Array();
		$formToken = $this->generator->generate($formName);
		if (!isset($_SESSION[$this->sessionKey][$formName])) {
			$_SESSION[$this->sessionKey][$formName] = Array();
		}
		$formId = UUID::randomBytes(32);
		$_SESSION[$this->sessionKey][$formName][$formId] = $formToken;
		return $formId . ':' . $formToken;
	}
}