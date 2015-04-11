<?php
/**
 * Created by PhpStorm.
 * User: jasondent
 * Date: 07/04/15
 * Time: 14:12
 */

namespace functionals\test;

require_once 'SampleData.php';

use functionals\Selector\Selector;
use functionals\Sequence;
use functionals\FnGen as fn;

class SelectorsTest extends \PHPUnit_Framework_TestCase {

    public function testSelect() {
        $a = Selector::make(SampleData::$sampleData, '.')->toArray();
        $b = Sequence::make(SampleData::$sampleData)->select('.')->toArray();
        $this->assertEquals($a, $b);
        $this->assertNotNull($a);

        $a = Sequence::make(SampleData::$sampleData)->select('..')->toArray();
        $b = Sequence::make(SampleData::$sampleData)->select('.')->select('.')->toArray();
        $this->assertEquals($a, $b);
        $this->assertNotNull($a);

        $c = Sequence::make(SampleData::$sampleData)->select('.products..price_options')->toArray();
        $d = Sequence::make(SampleData::$sampleData)->select('...price_options')->toArray();
        $this->assertEquals($c, $d);
        $e = Sequence::make(SampleData::$sampleData)->select('...price_options.')->toArray();
        $f = Sequence::make(SampleData::$sampleData)->select('...price_options.[]')->toArray();
        $this->assertEquals($d['price_options'], $e);
        $this->assertNotEquals($e, $f);
        $g = Sequence::make($e)->filter(fn\fnNotEmpty())->toArray();
        $this->assertEquals($g, $f);

    }

    public function testSelectConditions() {
        $f = Sequence::make(SampleData::$sampleData)->select('...price_options.[]')->toArray();
        $e = Sequence::make(SampleData::$sampleData)->select('...price_options.')->toArray();
        $g = Sequence::make($e)->filter(fn\fnNotEmpty())->toArray();
        $this->assertEquals($g, $f);
    }

}
