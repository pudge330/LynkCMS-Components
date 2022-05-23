<?php
namespace Lynk\Component\Security;

class KeyGenerator {
	protected static $alphanumeric = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	protected static $special = '!@#$%^&*()';
	protected static $extra = '-_ []{}<>~`+=,.;:/?|';
	public static function generateKey(int $length = 64, bool $special = true, bool $extra = true) : string {
		$characters = self::$alphanumeric;
		if ($special)
			$characters .= self::$special;
		if ($extra)
			$characters .= self::$extra;
		$maxLength = strlen($characters) - 1;
		$key = '';
		for ($i = 0; $i < $length; $i++) {
			$key .= substr($characters, random_int(0, $maxLength), 1);
		}
		return $key;
	}
}