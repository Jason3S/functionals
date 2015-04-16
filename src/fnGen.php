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
 * generate a function that:
 * always returns true
 * @return callable
 */
function fnTrue() {
    return function () {
        return true;
    };
}

/**
 * generate a function that:
 * always returns false
 * @return callable
 */
function fnFalse() {
    return function () {
        return false;
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
        return f\extractValue($value, $fieldName);
	};
}


function fnFieldEq($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (f\extractValue($doc, $fieldName) == $value);
    };
}

function fnFieldNe($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (f\extractValue($doc, $fieldName) == $value);
    };
}

function fnFieldLt($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (f\extractValue($doc, $fieldName) < $value);
    };
}

function fnFieldGt($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (f\extractValue($doc, $fieldName) > $value);
    };
}

function fnFieldLte($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (f\extractValue($doc, $fieldName) <= $value);
    };
}

function fnFieldGte($fieldName, $value) {
    return function ($doc) use ($fieldName, $value) {
        return (f\extractValue($doc, $fieldName) >= $value);
    };
}

function fnEq($value) {
    return function ($doc) use ($value) {
        return ($doc == $value);
    };
}

function fnEqEq($value) {
    return function ($doc) use ($value) {
        return ($doc === $value);
    };
}

function fnNe($value) {
    return function ($doc) use ($value) {
        return ($doc != $value);
    };
}

function fnNeEq($value) {
    return function ($doc) use ($value) {
        return ($doc !== $value);
    };
}

function fnLt($value) {
    return function ($doc) use ($value) {
        return ($doc < $value);
    };
}

function fnGt($value) {
    return function ($doc) use ($value) {
        return ($doc > $value);
    };
}

function fnLte($value) {
    return function ($doc) use ($value) {
        return ($doc <= $value);
    };
}

function fnGte($value) {
    return function ($doc) use ($value) {
        return ($doc >= $value);
    };
}

function fnRegEx($regEx) {
    return function ($doc) use ($regEx) {
        return preg_match($regEx, $doc);
    };
}

/**
 * Return a function that will ! any input value.
 * @return callable
 */
function fnNot() {
    return function ($doc) {
        return ! $doc;
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

    // There is only one function, use it instead.
    if (count($functions) == 1) {
        return $functions[0];
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
 * Generates a Functions that will:
 * Call all functions passing the passing the same value, the results will be AND'ed together.
 * NOTE: this function short circuits and returns on the first false value.
 * @param $functions -- both fnAnd(fn1, fn2, fn3) and fnAnd(array(fn1, fn2, fn3)) are valid.
 * @return callable
 */
function fnAnd($functions) {
    if (is_callable($functions)) {
        $functions = func_get_args();
    }

    $functions = \functionals\toIterator($functions);

    // if there is just one function, return it.
    if (count($functions) == 1) {
        return $functions[0];
    }

    return function () use ($functions) {
        $result = true;
        $args = func_get_args();
        foreach ($functions as $fn) {
            $result = $result && call_user_func_array($fn, $args);
            if (! $result) {
                break;
            }
        }
        return $result;
    };
}

/**
 * Generates a Functions that will:
 * Call all functions passing the passing the same value, the results will be OR'ed together.
 * NOTE: this function short circuits and returns on the first true value.
 * @param $functions
 * @return callable
 */
function fnOr($functions) {
    if (is_callable($functions)) {
        $functions = func_get_args();
    }

    $functions = \functionals\toIterator($functions);

    // if there is just one function, return it.
    if (count($functions) == 1) {
        return $functions[0];
    }

    return function () use ($functions) {
        $result = false;
        $args = func_get_args();
        foreach ($functions as $fn) {
            $result = $result || call_user_func_array($fn, $args);
            if ($result) {
                break;
            }
        }
        return $result;
    };
}


/**
 * @param callable $fn
 * @return callable
 */
function fnNotFn($fn) {
    return function () use ($fn) {
        return ! call_user_func_array($fn, func_get_args());
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

/**
 * Increments the counter whenever it is called and passes the call to $fn
 * @param $fn
 * @param $counter
 * @return callable
 */
function fnCallCountPassThrough($fn, &$counter) {
    return function () use ($fn, &$counter) {
        ++$counter;
        $args = func_get_args();
        return call_user_func_array($fn, $args);
    };
}

/**
 * Alias for fnExtract
 *
 * @param $fieldName
 * @return callable
 */
function fnPluck($fieldName) {
    return fnExtract($fieldName);
}
