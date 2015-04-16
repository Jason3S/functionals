<?php
namespace functionals\Selector;

use functionals\FnGen as fn;
use functionals as f;


class SelectorCompiler {
    const TOKEN_START               = 'start';
    const TOKEN_WORD                = 'word';
    const TOKEN_REGEX               = 'regex';
    const TOKEN_FIELD_SEPARATOR     = '.';
    const TOKEN_CONDITION_BEGIN     = '[';
    const TOKEN_CONDITION_END       = ']';
    const TOKEN_OP_EQUAL            = '=';
    const TOKEN_OP_NOT_EQUAL        = '!=';
    const TOKEN_OP_REL_COMPARISON   = '>';
    const TOKEN_CONDITION_SEPARATOR = ',';
    const TOKEN_GROUP_BEGIN         = '(';
    const TOKEN_GROUP_END           = ')';
    const TOKEN_SYMBOL              = '+-*$%#@';
    const TOKEN_END                 = 'end';
    const TOKEN                     = 'token';
    const VALUE                     = 'value';



	static $patterns = [
		'~(\w+|\\\[\[\\\]*.\-=])+~A'        => ['token' => self::TOKEN_WORD,                    'match' => 0],
		'~\'([^\']*)\'~A'                   => ['token' => self::TOKEN_WORD,                    'match' => 1],
		'~"([^"]*)"~A'                      => ['token' => self::TOKEN_WORD,                    'match' => 1],
        '~/([^/]|\\\/)*/[imsxADSUJu]*~A'    => ['token' => self::TOKEN_REGEX,                   'match' => 0],
		'~\.~A'                             => ['token' => self::TOKEN_FIELD_SEPARATOR,         'match' => 0],
		'~\s*(\[)\s*~A'                     => ['token' => self::TOKEN_CONDITION_BEGIN,         'match' => 1],
		'~\s*(\])\s*~A'                     => ['token' => self::TOKEN_CONDITION_END,           'match' => 1],
        '~\s*(\()\s*~A'                     => ['token' => self::TOKEN_GROUP_BEGIN,             'match' => 1],
        '~\s*(\))\s*~A'                     => ['token' => self::TOKEN_GROUP_END,               'match' => 1],
		'~\s*(,|\|\|?|&&?)\s*~A'            => ['token' => self::TOKEN_CONDITION_SEPARATOR,     'match' => 1],
        '~\s*(==?)\s*~A'                    => ['token' => self::TOKEN_OP_EQUAL,                'match' => 1],
        '~\s*(<>|!==?)\s*~A'                => ['token' => self::TOKEN_OP_NOT_EQUAL,            'match' => 1],
		'~\s*(<=|>=|<|>)\s*~A'              => ['token' => self::TOKEN_OP_REL_COMPARISON,       'match' => 1],
        '~\s*([+\-*^%$#@!])\s*~A'           => ['token' => self::TOKEN_SYMBOL,                  'match' => 1],  // these don't currently have any meaning.
	];

    static $grammar = [
        'start' => [
            'field_selectors',
        ],
        'field_selectors' => [
            'field_selector',
            'child_selector',
            'field_selector child_selector',                     // i.e university.teachers|students.  -- returns all the students and teaches in one list
        ],
        'child_selector' => [
            'field_separator',                               // i.e . -- same a flatten once
            'field_separator field_selectors',               // i.e universities..teachers|students. -- returns all the students and teaches from a list of universities
        ],
        'field_selector' => [
            'selectors'
        ],
        'selectors' => [
            'selector',
            'compound_selector',
            'selector_group',
        ],
        'selector_group' => [
            'group_begin selectors group_end',
        ],
        'compound_selector' => [
            'selector selector_operator selectors',
        ],
        'selector' => [
            'field',
            'condition',
            'field condition',
        ],
        'condition' => [
            'condition_begin condition_end',                // empty condition is allowed it matches against any non-empty object.
            'condition_begin conditions condition_end',
        ],
        'conditions' => [
            'condition_field_exists',
            'condition_expression',
            'compound_condition'
        ],
        'condition_field_exists' => [  // single
            'condition_field',
        ],
        'condition_expression' => [  // defined as left op right
            'condition_field operators condition_value',
            'condition_field regex_operators condition_regex'
        ],
        'compound_condition' => [   // defined as left op right
            'condition_field_exists condition_separator conditions',
            'condition_expression condition_separator conditions',
            'condition_group condition_separator conditions',
        ],
        'condition_group' => [
            'group_begin conditions group_end',
        ],
        'condition_field' => [
            'word',
            // 'regex',  // <-- this is possible but needs more work.
        ],
        'condition_value' => [
            'word',
        ],
        'condition_regex' => [
            'regex',
        ],
        'regex_operators' => [
            'reg_eq',
            'reg_ne',
        ],
        'operators' => [
            'op_eq',
            'op_ne',
            'op_rel',
        ],

        '[' => [],  // Used to mark the start of a semantic value example  condition [ ... ]
        ']' => [],  // Used to mark the end of a semantic value

        // leaf nodes
        'field'                 => self::TOKEN_WORD,
        'word'                  => self::TOKEN_WORD,
        'regex'                 => self::TOKEN_REGEX,
        'group_begin'           => self::TOKEN_GROUP_BEGIN,
        'group_end'             => self::TOKEN_GROUP_END,
        'op_eq'                 => self::TOKEN_OP_EQUAL,
        'op_ne'                 => self::TOKEN_OP_NOT_EQUAL,
        'op_rel'                => self::TOKEN_OP_REL_COMPARISON,
        'reg_eq'                => self::TOKEN_OP_EQUAL,
        'reg_ne'                => self::TOKEN_OP_NOT_EQUAL,
        'condition_separator'   => self::TOKEN_CONDITION_SEPARATOR,
        'selector_operator'     => self::TOKEN_CONDITION_SEPARATOR,
        'condition_begin'       => self::TOKEN_CONDITION_BEGIN,
        'condition_end'         => self::TOKEN_CONDITION_END,
        'field_separator'       => self::TOKEN_FIELD_SEPARATOR,
    ];

    /**
     * Convert a path into an array of tokens.
     *
     * @param $path
     * @return array
     * @throws SelectorCompilerException
     */
	public static function tokenize($path) {
		$tokens = [];
		while ($path) {
			$found = false;
			foreach (static::$patterns as $pattern => $tokenInfo) {
				if (preg_match($pattern, $path, $matches)) {
					$found = [self::TOKEN => $tokenInfo['token'], self::VALUE => $matches[$tokenInfo['match']]];
					$tokens[] = $found;
					$path = substr($path, strlen($matches[0]));
					break;
				}
			}
			if (! $found) {
				throw new SelectorCompilerException('Tokenize Error: Unable to tokenize "'.$path.'"');
			}
		}

		return $tokens;
	}

    /**
     * Converts a stream of tokens into a tree
     *
     * @param $statements
     * @return mixed
     */
    protected static function buildStatementTree($statements) {
        $offset = 0;

        $groupBegin = ['['=>true];
        $groupEnd   = [']'=>true];
        $ignore = ['group_begin'=>true, 'condition_begin'=>true,
                   'group_end'=>true, 'condition_end'=>true];

        $fnMakeTree = function() use (&$fnMakeTree, &$offset, $statements, $groupBegin, $groupEnd, $ignore) {
            $tree = [];

            while ($offset < count($statements)) {
                $token = $statements[$offset];
                $statement = $token['statement'];
                $offset += 1;
                if (isset($groupBegin[$statement])) {
                    $tree[count($tree)-1]['statements'] = $fnMakeTree();
                } else if (isset($groupEnd[$statement])) {
                    break;
                } else if (! isset($ignore[$statement])) {
                    $tree[] = $token;
                }
            }

            return $tree;
        };

        return $fnMakeTree();
    }

    /**
     * Process the tokens into statements
     *
     * @param $tokens
     * @return array|null - the statement tree
     */
    public static function processTokens($tokens) {
        $grammar = self::$grammar;

        $activeStatements = [
            ['statements' => ['start'], 'offset' => 0, 'processed' => [], 'stack' => []]
        ];


        while (! empty($activeStatements)) {
            $current = array_pop($activeStatements);

            if ($current['offset'] == count($tokens)) {
                if (empty($current['statements'])) {
                    return $current['processed'];
                }
            }

            if (empty($current['statements'])) {
                // We hit a leaf in the grammar tree, but there are still tokens.
                continue;
            }

            $statement = array_shift($current['statements']);
            $remainingStatements = $current['statements'];
            $g = $grammar[$statement];
            if (is_array($g)) {
                $processed = $current['processed'];
                $processed[] = [
                    'statement' => $statement
                ];

                if (! empty($g)) {
                    // Add them in reverse order since we pop them and want the simplest to be processed first.
                    foreach (array_reverse($g) as $statements) {
                        $statements = explode(' ', '[ '.$statements . ' ]');
                        $activeStatements[] = [
                            'statements' => array_merge($statements, $remainingStatements),
                            'offset' => $current['offset'],
                            'processed' => $processed,
                        ];
                    }
                } else {
                    // We have a no-op grammar statement add the remainder back into the active list.
                    // These are used to grouping statements.
                    $activeStatements[] = [
                        'statements' => $remainingStatements,
                        'offset' => $current['offset'],
                        'processed' => $processed,
                    ];
                }
            } else {
                $offset = $current['offset'];
                if ($offset < count($tokens) && $g == $tokens[$offset][self::TOKEN]) {
                    // We found a matching token.
                    $current['processed'][] = [
                        'statement' => $statement,
                        'token' => $tokens[$offset]
                    ];
                    $current['offset'] += 1;
                    $activeStatements[] = $current;
                } else {
                    // drop it, the token doesn't match.
                }
            }
        }

        return null;
    }

    /**
     * Take a path, tokenize it, and return the statement tree.
     *
     * @param $path
     * @return array|null
     * @throws SelectorCompilerException
     */
	public static function parsePath($path) {
        $tokens = static::tokenize($path);
        $statements = static::processTokens($tokens);
        $statementTree = static::buildStatementTree($statements);
        return $statementTree;
	}

    /**
     * Generate a function to bind the operator.
     *
     * @param $statement
     * @return callable
     * @throws SelectorCompilerException
     */
    protected static function genSelectorOperator($statement) {
        $op = $statement[self::TOKEN][self::VALUE];
        switch ($op) {
            case ',':
            case '|':
                return function ($fnLeft, $fnRight) {
                    return fn\fnOr($fnLeft, $fnRight);
                };
            case '&':
                return function ($fnLeft, $fnRight) {
                    return fn\fnAnd($fnLeft, $fnRight);
                };
            default:
                throw new SelectorCompilerException('Unexpected operator: '.$op);
        }
    }

    /**
     * Generate a function to bind the operator.
     *
     * @param $statement
     * @return callable
     * @throws SelectorCompilerException
     */
    protected static function genConditionsOperator($statement) {
        $op = $statement[self::TOKEN][self::VALUE];
        switch ($op) {
            case '|':
                return function ($fnLeft, $fnRight) {
                    return fn\fnOr($fnLeft, $fnRight);
                };
            case ',':
            case '&':
                return function ($fnLeft, $fnRight) {
                    return fn\fnAnd($fnLeft, $fnRight);
                };
            default:
                throw new SelectorCompilerException('Unexpected operator: '.$op);
        }
    }

    protected static function genComparison($statement) {
        $op = $statement[self::TOKEN][self::VALUE];
        switch ($op) {
            case '=':
                return function ($value) {
                    return fn\fnEq($value);
                };
            case '==':
                return function ($value) {
                    return fn\fnEqEq($value);
                };
            case '<>':
            case '!=':
                return function ($value) {
                    return fn\fnNe($value);
                };
            case '!==':
                return function ($value) {
                    return fn\fnNeEq($value);
                };
            case '<':
                return function ($value) {
                    return fn\fnLt($value);
                };
            case '>':
                return function ($value) {
                    return fn\fnGt($value);
                };
            case '<=':
                return function ($value) {
                    return fn\fnLte($value);
                };
            case '>=':
                return function ($value) {
                    return fn\fnGte($value);
                };
            default:
                throw new SelectorCompilerException('Unexpected operator: '.$op);
        }
    }

    protected static function genRegExComp($statement) {
        $op = $statement[self::TOKEN][self::VALUE];
        switch ($op) {
            case '=':
            case '==':
                return function ($regex) {
                    return fn\fnRegEx($regex);
                };
            case '!=':
            case '!==':
                return function ($regex) {
                    return fn\fnNotFn( fn\fnRegEx($regex) );
                };
            default:
                throw new SelectorCompilerException('Unexpected operator: '.$op);
        }
    }

    public static function fnExtract($fieldName) {
        return function ($value) use ($fieldName) {
            if ($value instanceof SelectReferenceWrapper) {
                $value = $value->getValue();
            }
            return f\extractValue($value, $fieldName);
        };
    }

    /**
     * Walks the statement tree and builds an array of functions to be chained together.
     *
     * @param $tokenTree
     * @return callable[]
     */
    public static function walkStatementTree($tokenTree) {
        $fnChain = [];

        foreach ($tokenTree as $statement) {
            switch ($statement['statement']) {
                case 'start':
                    $fnChain = static::walkStatementTree($statement['statements']);
                    break;
                case 'field_separator':
                    $fnChain[] = fn\fnChildren();
                    break;
                case 'field_selector':
                    $fnConditions = static::walkStatementTree($statement['statements']);
                    $fnChain[] = fn\fnFilter(fn\fnAnd($fnConditions));
                    break;
                case 'field':
                    $fieldName = $statement[self::TOKEN][self::VALUE];
                    $fnChain[] = function ($value, $key) use ($fieldName) { return (string)$key === (string)$fieldName; };
                    break;
                case 'selector_operator':
                    $fnChain[] = static::genSelectorOperator($statement);
                    break;
                case 'condition_separator':
                    $fnChain[] = static::genConditionsOperator($statement);
                    break;
                case 'condition':
                    $fnConditionChain = static::walkStatementTree($statement['statements']);
                    if (! empty($fnConditionChain)) {
                        $fnConditions = fn\fnAnd($fnConditionChain);
                    } else {
                        // return true if it can be iterated over
                        $fnConditions = function ($value) {
                            return ! empty($value) && (is_object($value) || is_array($value));
                        };
                    }
                    $fnChain[] = $fnConditions;
                    break;
                case 'compound_condition':
                case 'compound_selector':
                    list($fnLeft, $fnOp, $fnRight) = static::walkStatementTree($statement['statements']);
                    $fnChain[] = $fnOp($fnLeft, $fnRight);
                    break;
                case 'condition_field_exists':
                    list($fnGetField) = static::walkStatementTree($statement['statements']);
                    $fnChain[] = fn\fnChain($fnGetField, fn\fnIsSet());
                    break;
                case 'condition_field':
                    list($fieldName) = static::walkStatementTree($statement['statements']);
                    $fnChain[] = static::fnExtract($fieldName);
                    break;

                case 'condition_expression':
                    list($fnGetField, $fnOp, $valueRight) = static::walkStatementTree($statement['statements']);
                    $fnChain[] = fn\fnChain($fnGetField, $fnOp($valueRight));
                    break;

                case 'op_eq':
                case 'op_ne':
                case 'op_rel':
                    $fnChain[] = static::genComparison($statement);
                    break;

                case 'reg_eq':
                case 'reg_ne':
                    $fnChain[] = static::genRegExComp($statement);
                    break;
                //
                case 'regex':
                case 'word':
                    $fnChain[] = $statement[self::TOKEN][self::VALUE];
                    break;

                // @todo
                case 'field_selectors':
                case 'child_selector':
                case 'selector_group':
                case 'selector':
                case 'conditions':
                case 'condition_group':
                default:
                    if (! empty($statement['statements'])) {
                        $fnChain = array_merge($fnChain, static::walkStatementTree($statement['statements']));
                    }
                    break;
            }
        }

        return $fnChain;
    }


    /**
     * @param $tokenTree
     * @return callable
     */
    public static function compileTokenTree($tokenTree) {
        $fnChain = static::walkStatementTree($tokenTree);
        return fn\fnChain($fnChain);
    }

        /**
     * @param string $path
     * @return callable
     */
    public static function compilePath($path) {
        $tokenTree = static::parsePath($path);
        return static::compileTokenTree($tokenTree);
    }


    static function genFnChildren() {
        return function ($values, $key, $parent, $parents) {

        };
    }

}

class SelectorCompilerException extends \Exception {}
