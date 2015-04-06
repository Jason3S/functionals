<?php
namespace functionals;

/**
 * Covert arrays, objects, and other Traversable items into an iterator.
 *
 * @param Traversable $iterator - an iteratable item
 * @return \Iterator|\ArrayIterator|\Traversable
 */
function toIterator($iterator) {
	if ($iterator instanceof \Iterator) {
		return $iterator;
	}
	if ($iterator instanceof \IteratorAggregate) {
		return $iterator->getIterator();
	}
	if (\is_array($iterator) || \is_object($iterator)) {
		return new \ArrayIterator($iterator);
	}

	// Generate one
	$fn = function() use ($iterator) {
		foreach ($iterator as $key => $value) {
			yield $key => $value;
		}
	};
	return $fn();
}

/**
 * @param \Traversable $traversable
 * @param callable $fn($value, $key) - given a $value and $key, return the new value
 * @return \Generator
 */
function map(\Traversable $traversable, callable $fn) {
	foreach ($traversable as $key => $value) {
		yield $key => $fn($value, $key);
	}
}

/**
 * @param \Traversable $traversable
 * @param callable $fn($key, $value) - given a $key and $value, return the new key
 * @return \Generator
 */
function mapKeys(\Traversable $traversable, callable $fn) {
	foreach ($traversable as $key => $value) {
		yield $fn($key, $value) => $value;
	}
}

/**
 * @param \Traversable $traversable
 * @param callable $fn($value, $key) - given a $value and $key, return the new key
 * @return \Generator
 */
function keyBy(\Traversable $traversable, callable $fn) {
	foreach ($traversable as $key => $value) {
		yield $fn($value, $key) => $value;
	}
}

/**
 * @param \Traversable $traversable
 * @param callable $fn
 * @return \Generator
 */
function flip(\Traversable $traversable, callable $fn) {
	foreach ($traversable as $key => $value) {
		yield $value => $key;
	}
}

/**
 * @param \Traversable $traversable
 * @param callable $fn
 * @return \Generator
 */
function filter(\Traversable $traversable, callable $fn) {
	foreach ($traversable as $key => $value) {
		if ($fn($value, $key)) {
			yield $key => $value;
		}
	}
}

/**
 * @param \Traversable $traversable
 * @return \Generator
 */
function values(\Traversable $traversable) {
	foreach ($traversable as $value) {
		yield $value;
	}
}

/**
 * @param \Traversable $traversable
 * @return \Generator
 */
function keys(\Traversable $traversable) {
	foreach ($traversable as $key => $value) {
		yield $key;
	}
}

