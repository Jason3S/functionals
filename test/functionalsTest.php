<?php
namespace functionals;

require_once 'TestClassExtractValue.php';

class FunctionalsTest extends \PHPUnit_Framework_TestCase {

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

        // Test things that we do NOT want to be iterators.
        $this->assertNull(toIterator(5), '5');
        $string = 'hello';
        $this->assertNull(toIterator($string), 'String: '.$string);
	}

    public function testExtractValue() {
        $testObject = new TestClassExtractValue();
        $testArray = [
            'one' => 1,
            'two' => 2,
            3 => 'three'
        ];
        $testArrayAsObject = (object)$testArray;

        $this->assertEquals(1, extractValue($testArray, 'one'));
        $this->assertEquals(2, extractValue($testArray, 'two'));
        $this->assertEquals('three', extractValue($testArray, 3));

        $this->assertEquals(1, extractValue($testArrayAsObject, 'one'));
        $this->assertEquals(2, extractValue($testArrayAsObject, 'two'));
        $this->assertNull(extractValue($testArrayAsObject, 3));  // <-- not possible to access numeric indexed values

        $this->assertEquals($testObject->publicProperty, extractValue($testObject, 'publicProperty'));
        $this->assertEquals($testObject->getProtectedProperty(), extractValue($testObject, 'protectedProperty'));
        $this->assertEquals($testObject->getPrivateProperty(), extractValue($testObject, 'privateProperty'));
        $this->assertNull(extractValue($testObject, 'noAccess'));
        $this->assertTrue(extractValue($testObject, 'valueSet'));  // test isXXXX getter.

        $default = 'default';
        $this->assertEquals($default, extractValue($testObject, 'noAccess', $default));
    }

}