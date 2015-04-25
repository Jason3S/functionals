<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 12/04/15
 * Time: 20:21 PM
 */

namespace functionals;


class TestClassExtractValue {
    protected $protectedProperty = 'Value: protectedProperty';
    public $publicProperty = 'Value: publicProperty';
    protected $noAccess = 'Value: noAccess';
    private $privateProperty = 'Value privateProperty';
    protected $valueSet = true;

    /**
     * @return string
     */
    public function getProtectedProperty() {
        return $this->protectedProperty;
    }

    /**
     * @return string
     */
    public function getPrivateProperty() {
        return $this->privateProperty;
    }

    /**
     * @return boolean
     */
    public function isValueSet() {
        return $this->valueSet;
    }

}
