<?php
/**
 * Created by PhpStorm.
 * User: jasondent
 * Date: 16/08/2015
 * Time: 13:09
 */

namespace functionals;


class FnStringTest extends \PHPUnit_Framework_TestCase {

    public function testEncoding() {
        $this->assertEquals('UTF-8', mb_internal_encoding());
    }


    /**
     * @dataProvider stringTestProvider
     */
    public function testAgainstLibraryFunctions($testFn, $controlFn, $value, $isEqual) {
        $test = array($testFn, $controlFn, $value, $isEqual);
        /** @var \Closure $fn */
        $fn = call_user_func($testFn);
        if ($isEqual) {
            $this->assertEquals(call_user_func($controlFn, $value), $fn($value), implode(' -> ', $test));
        } else {
            $this->assertNotEquals(call_user_func($controlFn, $value), $fn($value), implode(' -> ', $test));
        }
    }

    public function stringTestProvider() {
        return [
            // Functional   Library function    value
            ['\functionals\fnStrToLower', 'strtolower', 'HeLlo', true],
            ['\functionals\fnStrToUpper', 'strtoupper', 'HeLlo', true],
            ['\functionals\fnStrToLower', 'strtoupper', 'HeLlo', false],
            ['\functionals\fnMbStrToLower', 'mb_strtolower', 'HéLloÖó', true],
            ['\functionals\fnMbStrToUpper', 'mb_strtoupper', 'HéLloÖóíö', true],
            ['\functionals\fnMbStrToLower', 'strtolower', 'HéLloÖó', false],
            ['\functionals\fnMbStrToUpper', 'strtoupper', 'HéLloÖóíö', false],
        ];
    }

}