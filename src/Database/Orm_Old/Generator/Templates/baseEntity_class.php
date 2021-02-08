<?php
namespace %namespaceRoot%;
{NL}
use Exception;
use %baseEntity% as EntityBase;
{NL}
class %className% extends EntityBase {
%variables%
	public function __construct($data = Array(), $tmpData = Array()) {
%defaultValues%
		parent::__construct($data, $tmpData);
	}
%gettersSetters%
	public static function map() {
		return Array(
%columnMap%
		);
	}
	public static function props() {
		return Array(%properties%);
	}
	public static function getAliasMap() {
		return Array(%aliasMap%);
	}
}