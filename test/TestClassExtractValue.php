<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 12/04/15
 * Time: 20:21 PM
 */

namespace functionals\FnGen;


class TestClassExtractValue {
    protected $protectedProperty = 'Value: protectedProperty';
    public $publicProperty = 'Value: publicProperty';
    protected $noAccess = 'Value: noAccess';
    private $privateProperty = 'Value privateProperty';

    public function getProtectedProperty() {
        return $this->protectedProperty;
    }

    public function getPrivateProperty() {
        return $this->privateProperty;
    }
}
