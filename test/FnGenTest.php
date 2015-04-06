<?php
namespace functionals\test;
use functionals\FnGen as FnGen;

class FnGenTest extends \PHPUnit_Framework_TestCase {

	public function testFnIdentity() {
		$fn = FnGen\fnIdentity();
		$this->assertEquals(5, $fn(5));
		$this->assertEquals(5, $fn('5'));
		$this->assertNotEquals('Hello', $fn('hello'));
	}



}