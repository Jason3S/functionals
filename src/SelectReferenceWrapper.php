<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 16/04/15
 * Time: 13:09 PM
 */

namespace functionals\Selector;


class SelectReferenceWrapper implements \IteratorAggregate {

    protected $srcObject;
    protected $isObject = false;
    protected $key;
    protected $parent;
    protected $parents;

    public function __construct(&$object, $key = null, $parent = null, $parents = []) {
        $this->srcObject = &$object;
        $this->key = $key;
        $this->parent = $parent;
        $this->parents = $parents;
        $this->isObject = (is_object($object) || is_array($object));
    }

    /**
     * @return \Generator
     */
    public function getIterator() {
        if ($this->isObject) {
            foreach ($this->srcObject as $key => &$value) {
                yield $key => new SelectReferenceWrapper($value, $key, $this->srcObject, array_merge($this->parents, [$this->srcObject]));
            }
        }
    }


    /**
     * @return mixed
     */
    public function getValue() {
        return $this->srcObject;
    }

    public function setValue($value) {
        $this->srcObject = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function getParents() {
        return $this->parents;
    }


}