<?php
namespace functionals;

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
