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

    public function testFnChain() {
        $fnInc = function ($n) {
            return $n + 1;
        };

        $fnChain = FnGen\fnChain($fnInc, $fnInc, $fnInc, $fnInc, $fnInc);
        $this->assertEquals(10, $fnChain(5));

        $fnChain = FnGen\fnChain(FnGen\fnIdentity(), FnGen\fnIdentity(), FnGen\fnIdentity(), FnGen\fnIdentity());
        $this->assertEquals(10, $fnChain(10));
    }

    public function testFnChildren() {

    }

}