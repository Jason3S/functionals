<?php
namespace functionals\Selector;


use SebastianBergmann\GlobalState\Exception;

class SelectorPathParser {
	const TOKEN_WORD = 'word';
	const TOKEN_FIELD_SEPERATOR = '.';
	const TOKEN_CONDITION_BEGIN = '[';
	const TOKEN_CONDITION_END = ']';
	const TOKEN_OPERATOR_COMPARISON = '=';
	const TOKEN = 'token';
	const VALUE = 'value';



	static $patterns = [
		'~(\w+|\\\[\[\\\]*.\-=])+~A' => ['token' => self::TOKEN_WORD,                   'match' => 0],
		'~\'([^\']+)\'~A'            => ['token' => self::TOKEN_WORD,                   'match' => 1],
		'~"([^"]+)"~A'               => ['token' => self::TOKEN_WORD,                   'match' => 1],
		'~\.~A'                      => ['token' => self::TOKEN_FIELD_SEPERATOR,        'match' => 0],
		'~\[~A'                      => ['token' => self::TOKEN_CONDITION_BEGIN,        'match' => 0],
		'~\]~A'                      => ['token' => self::TOKEN_CONDITION_END,          'match' => 0],
		'~\s*=|<=|>=|<|>\s*~A'       => ['token' => self::TOKEN_OPERATOR_COMPARISON,    'match' => 0],
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

	public static function parsePath($path) {
	}

}

class SelectorPathParserException extends \Exception {}
