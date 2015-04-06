<?php
namespace functionals;

class functionalsTest extends \PHPUnit_Framework_TestCase {

	protected static $furniture = [
		['name'=>'wooden table', 'legs'=>4, 'material'=>'wood', 'count'=>8],
		['name'=>'wooden chair', 'legs'=>4, 'material'=>'wood', 'count'=>22],
		['name'=>'metal table', 'legs'=>4, 'material'=>'metal', 'count'=>7],
		['name'=>'plastic table', 'legs'=>4, 'material'=>'plastic', 'count'=>19],
		['name'=>'plastic chair', 'legs'=>4, 'material'=>'plastic', 'count'=>81],
	];

	public function testToIterator() {
		$values = range(0,100);
		$iter = toIterator($values);
		$this->assertInstanceOf('Iterator', $iter);
		$this->assertEquals($values, \iterator_to_array($iter));

		$this->assertEquals($iter, toIterator($iter));
		$this->assertTrue($iter === toIterator($iter));

		$this->assertEquals(static::$furniture, \iterator_to_array(toIterator(static::$furniture)));

		$object = (object)static::$furniture[0]; // get an object.
		$this->assertInstanceOf('Iterator', toIterator($object));
		$this->assertEquals($object, (object)(\iterator_to_array(toIterator($object))));
	}
}