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

    /**
     * @param callable $fn($value, $key)
     * @return Sequence
     */
	public function map(callable $fn) {
		return Sequence(map($this->getIterator(), $fn));
	}

    /**
     * @param callable $fn($key, $value) - given a $key and $value, return the new key
     * @return Sequence
     */
    public function mapKeys(callable $fn) {
        return Sequence(mapKeys($this->getIterator(), $fn));
    }

    /**
     * @param callable $fn($value, $key)
     * @return Sequence
     */
    public function keyBy(callable $fn) {
        return Sequence(keyBy($this->getIterator(), $fn));
    }

    /**
     * @param callable $fn($value, $key)
     */
    public function walk(callable $fn) {
        walk($this->getIterator(), $fn);
    }

    /**
     * @param callable $fn($value, $key)
     * @return Sequence
     */
    public function filter(callable $fn) {
		return Sequence(filter($this->getIterator(), $fn));
	}

    /**
     * Return the just the values.  The keys will start from 0.
     * @return Sequence
     */
    public function values() {
        return Sequence(values($this->getIterator()));
    }

    /**
     * @return Sequence -- Map the keys into values..
     */
    public function keys() {
        return Sequence(keys($this->getIterator()));
    }

    public function select($path) {
        return Sequence(Selector::make($this->getIterator(), $path));
    }

    public function selectAndMap($path, $fnMap) {
        return Sequence(map($this->getIterator(), function($value) use ($path, $fnMap) {
            return Selector::selectAndApplyMap($value, $path, $fnMap);
        }));
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
