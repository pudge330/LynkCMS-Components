<?php

namespace LibraryTests;

class SampleTest extends \PHPUnit_Framework_TestCase {
	public function testTrueEqualsTrue() {
		$this->assertTrue(true);
	}
	public function testTrueEqualsTrueToFail() {
		$this->assertTrue(false);
	}
}