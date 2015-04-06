<?php
namespace functionals\test;
use functionals\Sequence as Sequence;
use functionals\FnGen as FnGen;

class SequenceTest extends \PHPUnit_Framework_TestCase {

	protected static $furniture = [
		['name'=>'wooden table', 'legs'=>4, 'material'=>'wood', 'count'=>8],
		['name'=>'wooden chair', 'legs'=>4, 'material'=>'wood', 'count'=>22],
		['name'=>'metal table', 'legs'=>4, 'material'=>'metal', 'count'=>7],
		['name'=>'plastic table', 'legs'=>4, 'material'=>'plastic', 'count'=>19],
		['name'=>'plastic chair', 'legs'=>4, 'material'=>'plastic', 'count'=>81],
	];


	public function testMap() {
		$furnitureNames = Sequence::make(static::$furniture)->map(FnGen\fnExtract('name'))->toArray();
		$this->assertEquals(array_map(FnGen\fnExtract('name'), static::$furniture), $furnitureNames);
		$this->assertNotEmpty($furnitureNames);
	}

	public function testMapKeys() {
		$furnitureNames = Sequence::make(static::$furniture)->map(FnGen\fnExtract('name'))->toArray();
	}
}