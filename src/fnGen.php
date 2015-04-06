<?php
namespace functionals\FnGen;

/**
 * generate an identity function
 *
 * @return callable
 */
function fnIdentity() {
	return function ($value) {
		return $value;
	};
}

/**
 * Swaps the first to parameters and passes them through to $fn
 *
 * @param callable $fn($b, $a);
 * @return callable
 */
function fnSwapParams(callable $fn) {
	return function ($a, $b) use ($fn) {
		return $fn($b, $a);
	};
}

/**
 * Generate a function that will
 * Extract from an array $value[$fieldName] or an object $value->$fieldName
 *
 * @param string $fieldName
 * @return callable
 */
function fnExtract($fieldName) {
	return function ($value) use ($fieldName) {
		if (is_array($value) || $value instanceof \ArrayAccess) {
			return isset($value[$fieldName]) ? $value[$fieldName] : null;
		}
		if (is_object($value) && property_exists($value, $fieldName)) {
			return $value->{$fieldName};
		}
		return null;
	};
}
