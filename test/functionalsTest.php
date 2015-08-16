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

    public function famousPeopleProvider() {
        return [
            [[
                'name_first' => 'John',
                'name_middle' => 'F',
                'name_last' => 'Kennedy',
                'occupation' => 'President',
                'date_birth' => '1917-05-29',
                'date_death' => '1963-11-23',
                'gender' => 'm',
            ]],
            [[
                'name_first' => 'Alfred',
                'name_middle' => 'Joseph',
                'name_last' => 'Hitchcock',
                'occupation' => 'Author',
                'date_birth' => '1899-08-13',
                'date_death' => '1980-04-29',
                'gender' => 'm',
            ]],
            [[
                'name_first' => 'Dianna',
                'name_last' => 'Spencer',
                'occupation' => 'Princess of Wales',
                'date_birth' => '1961-07-01',
                'date_death' => '1997-08-31',
                'gender' => 'f',
            ]],
        ];
    }

    /**
     *
     * @param array $person
     * @dataProvider famousPeopleProvider
     */
    public function testFnMapper($person) {
        $fnAge = function($v) {
            $fromDate = extractValue($v, 'date_birth');
            $toDate = extractValue($v, 'date_death', date('Y-m-d'));

            $age = 0;

            if ($fromDate) {
                $fromDateTime = new \DateTime($fromDate);
                $toDateTime = new \DateTime($toDate);
                $diff = $fromDateTime->diff($toDateTime);
                $age = $diff->format('%y');
            }

            return $age;
        };

        $mapDefinition = [
            // dst field  => src field or map function
            'Last Name'   => 'name_last',
            'First Name'  => 'name_first',
            'Middle Name' => 'name_middle',
            'Age'         => $fnAge,
            'Gender'      => fnChain(fnExtract('gender'), fnMapValue(['m' => 'male', 'f' => 'female'])),
        ];

        $value = mapper($person, $mapDefinition);
        $this->assertNotEmpty($value);
    }

}