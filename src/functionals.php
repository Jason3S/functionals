<?php
namespace functionals;

/**
 * Covert arrays, objects, and other Traversable items into an iterator.
 *
 * @param \Traversable|array $iterator - an iteratable item
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

    return null;
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
 * @param callable $fn
 */
function walk(\Traversable $traversable, callable $fn) {
    foreach ($traversable as $key => $value) {
        $fn($value, $key);
    }
}

/**
 * Alias of walk.
 *
 * @param \Traversable $traversable
 * @param callable $fn
 */
function each(\Traversable $traversable, callable $fn) {
    walk($traversable, $fn);
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

/**
 * stop after limit number of items.
 *
 * @param \Traversable $traversable
 * @param int $limit
 * @return \Generator
 */
function limit(\Traversable $traversable, $limit) {
    if ($limit > 0) {
        foreach ($traversable as $key => $value) {
            yield $key => $value;
            if (--$limit < 1) {
                break;
            }
        }
    }
}

/**
 * Skip offset number of items
 *
 * @param \Traversable $traversable
 * @param $offset
 * @return \Generator
 */
function offset(\Traversable $traversable, $offset) {
    foreach ($traversable as $key => $value) {
        if ($offset-- < 1) {
            yield $key => $value;
        }
    }
}

/**
 * @param \Traversable $traversable
 * @return \Generator
 */
function children(\Traversable $traversable) {
    foreach ($traversable as $parent) {
        if ($iter = toIterator($parent)) {
            foreach ($iter as $key=>$value) {
                yield $key => $value;
            }
        }
    }
}

/**
 * Combines the key and value into a tuple.
 *
 * @param \Traversable $traversable
 * @return \Generator
 */
function pairKeyValues(\Traversable $traversable) {
    foreach ($traversable as $key => $value) {
        yield [$key, $value];
    }
}

function extractValue($doc, $fieldName, $default = null) {
    if (is_array($doc) || $doc instanceof \ArrayAccess) {
        return isset($doc[$fieldName]) ? $doc[$fieldName] : $default;
    }
    if (is_object($doc)) {
        if (isset($doc->{$fieldName})) {
            return $doc->{$fieldName};
        }
        if (property_exists($doc, $fieldName)) {
            $getMethod = 'get'.$fieldName;
            if (method_exists($doc, $getMethod)) {
                return $doc->{$getMethod}();
            }
            $getMethod = 'is'.$fieldName;
            if (method_exists($doc, $getMethod)) {
                return $doc->{$getMethod}();
            }
        }
    }
    return $default;
}

function hasField($doc, $fieldName) {
    if (is_array($doc)) {
        return array_key_exists($fieldName, $doc);
    }

    if($doc instanceof \ArrayAccess) {
        return $doc->offsetExists($fieldName);
    }

    if (is_object($doc)) {
        // Quick check
        if (isset($doc->{$fieldName})) {
            return true;
        }
        if (property_exists($doc, $fieldName)) {
            // The property exists, now determine if it is null, or not public
            $vars = get_object_vars($doc);
            $getMethod = 'get'.$fieldName;

            return array_key_exists($fieldName, $vars) || method_exists($doc, $getMethod);
        }
    }
    return false;
}

/**
 * @param string       $value
 * @param array|object $arrayMap
 * @param null|mixed   $default
 * @return null
 */
function mapValue($value, $arrayMap, $default = null) {
    return extractValue($arrayMap, $value, $default);
}

/**
 * @param array|object $doc
 * @param array $mapDefinition
 * @return array
 */
function mapper($doc, $mapDefinition) {
    $result = array();

    foreach ($mapDefinition as $field => $src) {
        if ($src === '*') {
            $srcValue = $doc;
        } elseif ($src instanceof \Closure) {
            $srcValue = $src($doc);
        } else {
            $srcValue = extractValue($doc, $src);
        }

        if ($field === '*') {
            if (!empty($srcValue) && (is_array($srcValue) || is_object($srcValue))) {
                $result = array_merge($result, (array)$srcValue);
            }
        } else {
            $result[$field] = $srcValue;
        }
    }

    return $result;
}

