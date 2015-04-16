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
	foreach ($iterator as $key => $value) {
        yield $key => $value;
	}
}


function Selector($iterator, $path = null) {
    return Selector::make($iterator, $path);
}


class Selector implements \IteratorAggregate {
    static $compiledSelectors = [];
    static $compiledSelectorsDesiredMaxSize = 100;
    static $compiledSelectorsLimit = 110;

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

    public static function calcPathKey($path) {
        if (strlen($path) > 32) {
            return md5($path);
        }
        return $path;
    }

    /**
     * @param string $path
     * @return null|callable
     */
    public static function getCachedPath($path) {
        $cacheKey = static::calcPathKey($path);
        if (isset(static::$compiledSelectors[$cacheKey])) {
            return static::$compiledSelectors[$cacheKey];
        }
        return null;
    }

    /**
     * @param string $path
     * @param callable $compiledPath
     */
    public static function cachePath($path, callable $compiledPath) {
        $cacheKey = static::calcPathKey($path);
        static::$compiledSelectors[$cacheKey] = $compiledPath;
        if (count(static::$compiledSelectors) > static::$compiledSelectorsLimit) {
            // Keep the last n.
            static::$compiledSelectors = array_slice(static::$compiledSelectors, - static::$compiledSelectorsDesiredMaxSize);
        }
    }

	public static function applyPath($selector, $path) {
        $fnSelector = static::getCachedPath($path);

        if (!$fnSelector) {
            $fnCompiledPath = SelectorCompiler::compilePath($path);
            $fnSelector = function($iterator) use ($fnCompiledPath) {
                return Selector($fnCompiledPath($iterator));
            };
            static::cachePath($path, $fnSelector);
        }

        return $fnSelector($selector);
	}


	public static function make($iterator, $path = null) {
        $selector = new Selector(\functionals\toIterator($iterator));

		if ($path) {
			$selector = static::applyPath($selector, $path);
		}

	    return $selector;
    }

    public static function selectAndApplyMap($document, $path, $fnMap) {
        foreach (Selector::make(new SelectReferenceWrapper($document), $path) as $wrapperKey => $wrapper) {
            /** @var SelectReferenceWrapper $wrapper */
            $wrapper->setValue($fnMap($wrapper->getValue(), $wrapper->getKey(), $wrapper->getParent(), $wrapper->getParents()));
        }

        return $document;
    }
}