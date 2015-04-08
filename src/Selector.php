<?php
namespace functionals\Selector;

use functionals\FnGen as FnGen;

/*
  Syntax options:

*/


function selectField($iterator, $fieldName) {
    foreach ($iterator as $key => $value) {
        if (is_array($value)) {
            if (array_key_exists($fieldName, $value)) {
                yield $value[$fieldName];
            }
        } else if ($value instanceof \ArrayAccess) {
            if ($value->offsetExists($fieldName)) {
                yield $value[$fieldName];
            }
        } else if (is_object($value)) {
            if (property_exists($value, $fieldName)) {
                yield $value->{$fieldName};
            }
        }
    }
}

function selectFilter($iterator, $fn) {
    foreach ($iterator as $value) {
        if ($fn($value)) {
            yield $value;
        }
    }
}

function selectChildren($iterator) {
	foreach ($iterator as $values) {
		foreach ($values as $key => $value) {
			yield $key => $value;
		}
	}
}


function Selector($iterator, $path = null) {
    return Selector::make($iterator, $path);
}


class Selector implements \IteratorAggregate {

    protected $iterator;

    public function getIterator() {
        return $this->iterator;
    }

    public function __construct($iterator) {
        $this->iterator = $iterator;
    }

    public function selectField($fieldName) {
        return Selector(selectField($this->iterator, $fieldName));
    }

    public function selectConditionFieldEquals($fieldName, $value) {
        return Selector(selectField($this->iterator, FnGen\fnFieldEq($fieldName, $value)));
    }

	public function selectChildren() {
		return Selector(selectChildren($this->iterator));
	}

    public function toArray() {
        return iterator_to_array($this->iterator);
    }

	public static function applyPath($selector, $path) {


		return $selector;
	}


	public static function make($iterator, $path = null) {
        $selector = new Selector(\functionals\toIterator($iterator));

		if ($path) {
			$selector = static::applyPath($selector, $path);
		}

	    return $selector;
    }

}