<?php
namespace BGStudios\Component\Form\Security;

class FormTokenizer {
	protected static $secretKey = null;
	protected $generator;
	protected $sessionKey;
	public function __construct($generator = null, $sessionKey = '_formTokens') {
		if (!$generator)
			$generator = static::create();
		$this->generator = $generator;
		$this->sessionKey = $sessionKey;
	}
	public static function setSecret($secret) {
		self::$secretKey = $secret;
	}
	public static function createToken($secret = null) {
		if (!$secret)
			$secret = self::$secretKey ?: CSRFToken::getRandomBytes(128);
		return new CSRFToken($secret);
	}
	public static function create($secret = null) {
		return new FormTokenizer(static::createToken($secret));
	}
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
	public function registerToken($formName) {
		if (!isset($_SESSION[$this->sessionKey]))
			$_SESSION[$this->sessionKey] = Array();
		$formToken = $this->generator->generate($formName);
		if (!isset($_SESSION[$this->sessionKey][$formName])) {
			$_SESSION[$this->sessionKey][$formName] = Array();
		}
		$formId = $this->generator->getRandomBytes(32);
		$_SESSION[$this->sessionKey][$formName][$formId] = $formToken;
		return $formId . ':' . $formToken;
	}
}