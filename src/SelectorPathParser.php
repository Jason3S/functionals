<?php
namespace functionals\Selector;


use SebastianBergmann\GlobalState\Exception;

class SelectorPathParser {
    const TOKEN_START = 'start';
	const TOKEN_WORD = 'word';
	const TOKEN_FIELD_SEPARATOR = ',';
	const TOKEN_CONDITION_BEGIN = '[';
	const TOKEN_CONDITION_END = ']';
	const TOKEN_OPERATOR_COMPARISON = '=';
	const TOKEN_CONDITION_SEPARATOR = ',';
    const TOKEN_GROUP_BEGIN = '(';
    const TOKEN_GROUP_END = ')';
    const TOKEN_END = 'end';
	const TOKEN = 'token';
	const VALUE = 'value';



	static $patterns = [
		'~(\w+|\\\[\[\\\]*.\-=])+~A' => ['token' => self::TOKEN_WORD,                   'match' => 0],
		'~\'([^\']+)\'~A'            => ['token' => self::TOKEN_WORD,                   'match' => 1],
		'~"([^"]+)"~A'               => ['token' => self::TOKEN_WORD,                   'match' => 1],
		'~\.~A'                      => ['token' => self::TOKEN_FIELD_SEPARATOR,        'match' => 0],
		'~\s*(\[)\s*~A'              => ['token' => self::TOKEN_CONDITION_BEGIN,        'match' => 1],
		'~\s*(\])\s*~A'              => ['token' => self::TOKEN_CONDITION_END,          'match' => 1],
        '~\s*(\()\s*~A'              => ['token' => self::TOKEN_GROUP_BEGIN,            'match' => 1],
        '~\s*(\))\s*~A'              => ['token' => self::TOKEN_GROUP_END,              'match' => 1],
		'~\s*(,|\|\|?|&&?)\s*~A'     => ['token' => self::TOKEN_CONDITION_SEPARATOR,    'match' => 1],
		'~\s*(=|<=|>=|<|>)\s*~A'     => ['token' => self::TOKEN_OPERATOR_COMPARISON,    'match' => 1],
	];

    static $grammar = [
        'start' => [
            'selectors',
        ],
        'selectors' => [
            'selector',
            'selector field-separator selectors'
        ],
        'selector' => [
            'word',
            'word condition',
            'condition',
            'group-begin selector group-end',
            'group-begin selector selector-operator selector group-end',
        ],
        'condition' => [
            'condition-begin conditions condition-end',
        ],
        'conditions' => [
            'word operator word',
            'word operator conditions',
            'group-begin conditions group-end',
        ],

        // leaf nodes
        'word'                  => self::TOKEN_WORD,
        'group-begin'           => self::TOKEN_GROUP_BEGIN,
        'group-end'             => self::TOKEN_GROUP_END,
        'operator'              => self::TOKEN_OPERATOR_COMPARISON,
        'condition-operator'    => self::TOKEN_CONDITION_SEPARATOR,
        'selector-operator'     => self::TOKEN_CONDITION_SEPARATOR,
        'condition-begin'       => self::TOKEN_CONDITION_BEGIN,
        'condition-end'         => self::TOKEN_CONDITION_END,
        'field-separator'       => self::TOKEN_FIELD_SEPARATOR,
    ];


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
				throw new SelectorPathParserException('Tokenize Error: Unable to tokenize "'.$path.'"');
			}
		}

		return $tokens;
	}

    public static function parseTokens($tokens) {
        $grammar = self::$grammar;

        $activeStatements = [
            ['statements' => ['start'], 'offset' => 0, 'processed' => []]
        ];


        while (! empty($activeStatements)) {
            $current = array_pop($activeStatements);

            if ($current['offset'] == count($tokens)) {
                if (empty($current['statements'])) {
                    return $current['processed'];
                } else {
                    // We ran out of tokens to process, but more statements are expected, so this isn't the right branch.
                    // Drop it.
                    continue;
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
                // Add them in reverse order since we pop them and want the simplest to be processed first.
                foreach (array_reverse($g) as $statements) {
                    $statements = explode(' ', $statements);
                    $activeStatements[] = [
                        'statements' => array_merge($statements, $remainingStatements),
                        'offset' => $current['offset'],
                        'processed' => $processed,
                    ];
                }
            } else {
                $offset = $current['offset'];
                if ($g == $tokens[$offset][self::TOKEN]) {
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

	public static function parsePath($path) {
	}

}

class SelectorPathParserException extends \Exception {}
