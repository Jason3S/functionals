<?php
namespace functionals\FnGen;

class FnGenTest extends \PHPUnit_Framework_TestCase {

	public function testFnIdentity() {
		$fn = fnIdentity();
		$this->assertEquals(5, $fn(5));
		$this->assertEquals(5, $fn('5'));
		$this->assertNotEquals('Hello', $fn('hello'));
	}

    public function testFnCallCountPassThrough() {
        $counter = 0;
        $fn = fnCallCountPassThrough(fnIdentity(), $counter);

        foreach(range(0,10) as $v) {
            $this->assertEquals($v, $fn($counter));
        }
    }

    public function testFnChain() {
        $fnInc = function ($n) {
            return $n + 1;
        };

        $fnChain = fnChain($fnInc, $fnInc, $fnInc, $fnInc, $fnInc);
        $this->assertEquals(10, $fnChain(5));

        $fnChain = fnChain(fnIdentity(), fnIdentity(), fnIdentity(), fnIdentity());
        $this->assertEquals(10, $fnChain(10));
    }

    public function testFnTrue() {
        $fn = fnTrue();
        $this->assertTrue(true === $fn());
        $this->assertTrue(true === $fn());
        $this->assertTrue(true === $fn());
    }

    public function testFnFalse() {
        $fn = fnFalse();
        $this->assertTrue(false === $fn());
        $this->assertTrue(false === $fn());
        $this->assertTrue(false === $fn());
    }

    public function testFnNot() {
        $fn = fnNot();
        $this->assertTrue(false === $fn(true));
        $this->assertTrue(false === $fn(true));
        $this->assertTrue(false === $fn(1));
        $this->assertTrue(false === $fn("hello"));
        $this->assertTrue(false === $fn("."));
        $this->assertTrue(false === $fn("false"));
        $this->assertTrue(false === $fn([1,2]));
        $this->assertTrue(false === $fn([0]));
        $this->assertTrue(true === $fn(false));
        $this->assertTrue(true === $fn(false));
        $this->assertTrue(true === $fn(0));
        $this->assertTrue(true === $fn(null));
        $this->assertTrue(true === $fn('0'));
        $this->assertTrue(true === $fn([]));
    }

    public function testFnOr() {
        $fn = fnOr(fnTrue(), fnFalse(), fnTrue());
        $this->assertTrue($fn());

        $fn = fnOr(fnFalse(), fnFalse(), fnFalse());
        $this->assertFalse($fn());

        // Make sure it circuits.
        $count = 0;
        $fnTrue = fnCallCountPassThrough(fnTrue(), $count);
        $fnFalse = fnCallCountPassThrough(fnFalse(), $count);

        $count = 0;
        $fn = fnOr($fnTrue);
        $fn();
        $this->assertEquals(1, $count);

        $count = 0;
        $fn = fnOr($fnTrue, $fnTrue, $fnTrue);
        $fn();
        $this->assertEquals(1, $count);

        $count = 0;
        $fn = fnOr($fnFalse, $fnFalse, $fnTrue, $fnTrue, $fnTrue);
        $fn();
        $this->assertEquals(3, $count);

        // assert that if there is only one function passed it, it become itself.
        $fn = fnTrue();
        $this->assertTrue($fn === fnOr($fn));
    }

    public function testFnAnd() {
        $fn = fnAnd(fnTrue(), fnFalse(), fnTrue());
        $this->assertFalse($fn());

        $fn = fnAnd(fnTrue(), fnTrue(), fnTrue());
        $this->assertTrue($fn());

        // Make sure it circuits.
        $count = 0;
        $fnTrue = fnCallCountPassThrough(fnTrue(), $count);
        $fnFalse = fnCallCountPassThrough(fnFalse(), $count);

        $count = 0;
        $fn = fnAnd($fnTrue);
        $fn();
        $this->assertEquals(1, $count);

        $count = 0;
        $fn = fnAnd($fnTrue, $fnTrue, $fnFalse, $fnTrue, $fnTrue);
        $fn();
        $this->assertEquals(3, $count);

        $count = 0;
        $fn = fnAnd($fnFalse, $fnFalse, $fnTrue, $fnTrue, $fnTrue);

        $fn();
        $this->assertEquals(1, $count);

        // assert that if there is only one function passed it, it become itself.
        $fn = fnTrue();
        $this->assertTrue($fn === fnAnd($fn));

        $this->assertFalse(fnTrue() === fnTrue());
    }

}
