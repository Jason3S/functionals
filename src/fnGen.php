<?php
namespace functionals\FnGen;

use functionals as f;

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

function extractValue($doc, $fieldName) {
    if (is_array($doc) || $doc instanceof \ArrayAccess) {
        return isset($doc[$fieldName]) ? $doc[$fieldName] : null;
    }
    if (is_object($doc) && property_exists($doc, $fieldName)) {
        return $doc->{$fieldName};
    }
    return null;
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
        return extractValue($value, $fieldName);
	};
}


function fnFieldEq($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (extractValue($doc, $fieldName) == $value);
    };
}

function fnFieldLt($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (extractValue($doc, $fieldName) < $value);
    };
}

function fnFieldGt($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (extractValue($doc, $fieldName) > $value);
    };
}

function fnFieldLte($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (extractValue($doc, $fieldName) <= $value);
    };
}

function fnFieldGte($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (extractValue($doc, $fieldName) >= $value);
    };
}

/**
 * @param callable|callable[] $functions
 * @return callable
 */
function fnChain($functions) {
    if (is_callable($functions)) {
        $functions = func_get_args();
    }

    $functions = \functionals\toIterator($functions);

    return function () use ($functions) {
        $args = func_get_args();
        foreach ($functions as $fn) {
            $args = [ call_user_func_array($fn, $args) ];
        }
        return array_shift($args);
    };
}

/**
 * @return callable
 */
function fnChildren() {
    return function ($iterator) {
        return f\children($iterator);
    };
}

/**
 * @param $fn
 * @return callable
 */
function fnFilter($fn) {
    return function ($iterator) use ($fn) {
        return f\filter($iterator, $fn);
    };
}

/**
 * @return callable
 */
function fnNotEmpty() {
    return function ($value) {
        return ! empty($value);
    };
}

/**
 * @return callable
 */
function fnEmpty() {
    return function ($value) {
        return empty($value);
    };
}

/**
 * @return callable
 */
function fnIsSet() {
    return function ($value) {
        return isset($value);
    };
}