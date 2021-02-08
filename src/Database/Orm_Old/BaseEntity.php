<?php
namespace BGStudios\Component\Database\Orm;

use BGStudios\Component\Database\AbstractEntity;

class BaseEntity extends AbstractEntity {
	public function __construct($data = Array(), $tmpData = Array()) {
		$props = static::props();
		foreach ($data as $key => $value) {
			$key = $this->toCamelCase($key);
			if (!in_array($key, $props)) {
				$tmpData[$key] = $value;
				unset($data[$key]);
			}
		}
		parent::__construct($data, $tmpData);
	}
	public function __call($name, $arguments) {
		if (preg_match('/^(get|set|echo|print)(\w+)$/', $name, $match)) {
			$action = $match[1];
			$field = $match[2];
			$alias = static::resolveAlias($field);
			return parent::__call("{$action}{$alias}", $arguments);
		}
		else
			return parent::__call($name, $arguments);
	}
	public static function map() {
		return Array();
	}
	public static function props() {
		return Array();
	}
	public static function getAliasMap() {
		return Array();
	}
	public function export($scalar = false, $mapped = false) {
		$map = static::map();
		$return = Array();
		foreach ($map as $key => $value) {
			$key = !$mapped ? $this->toCamelCase($key) : $key;
			$return[$key] = $this->{"get{$value}"}($scalar);
		}
		return $return;
	}
	public function exportMapped($scalar = false) {
		return $this->export($scalar, true);
	}
	public function exportScalar($mapped = false) {
		return $this->export(true, $mapped);
	}
	public function exportScalarMapped() {
		return $this->export(true, true);
	}
	public static function resolveAlias($name) {
		$aliases = static::getAliasMap();
		return array_key_exists($name, $aliases) ? $aliases[$key] : $name;
	}
}