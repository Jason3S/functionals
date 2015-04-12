<?php
namespace functionals;

use functionals\Selector\Selector;

function Sequence($iterator) {
	return Sequence::make($iterator);
}

class Sequence implements \IteratorAggregate {
	protected $iterator;

	public function __construct($iterator) {
		$this->iterator = $iterator;
	}

	public function getIterator() {
		return $this->iterator;
	}

	public function map(callable $fn) {
		return Sequence(map($this->getIterator(), $fn));
	}

	public function filter(callable $fn) {
		return Sequence(filter($this->getIterator(), $fn));
	}

    public function values() {
        return Sequence(values($this->getIterator()));
    }

    public function keys() {
        return Sequence(keys($this->getIterator()));
    }

    public function select($path) {
        return Sequence(Selector::make($this->getIterator(), $path));
    }

    public function pairKeyValues() {
        return Sequence(pairKeyValues($this->getIterator()));
    }

    public function limit($n) {
        return Sequence(limit($this->getIterator(), $n));
    }

    public function offset($n) {
        return Sequence(offset($this->getIterator(), $n));
    }

	public function reduce($init, callable $fn) {
		$current = $init;
		foreach ($this as $key => $value) {
			$current = $fn($current, $value, $key);
		}
		return $current;
	}

	public function toArray() {
		return \iterator_to_array($this->getIterator());
	}

	public function toObject() {
		return (object) $this->toArray();
	}

	public static function make($iterator) {
		return new Sequence(\functionals\toIterator($iterator));
	}
}
