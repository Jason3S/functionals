<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 08/04/15
 * Time: 09:09 AM
 */

namespace functionals\test;

use functionals\Selector\SelectorPathParser as SelectorPathParser;

class SelectorPathParserExceptionTest extends \PHPUnit_Framework_TestCase {
	public function testTokenizer() {
		$tests = [
			'product' => [
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD, SelectorPathParser::VALUE => 'product']
			],
			'\'product\'' => [
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD, SelectorPathParser::VALUE => 'product']
			],
			'"product"' => [
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD, SelectorPathParser::VALUE => 'product']
			],
			'product.name' => [
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'product'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_FIELD_SEPERATOR,    SelectorPathParser::VALUE => '.'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'name'],
			],
			'products[type=55].name' => [
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'products'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_CONDITION_BEGIN,    SelectorPathParser::VALUE => '['],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'type'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_OPERATOR_COMPARISON,SelectorPathParser::VALUE => '='],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => '55'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_CONDITION_END,      SelectorPathParser::VALUE => ']'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_FIELD_SEPERATOR,    SelectorPathParser::VALUE => '.'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'name'],
			],
		];

		foreach ($tests as $testPath => $expectedResult) {
			$this->assertEquals($expectedResult, SelectorPathParser::tokenize($testPath));
		}
	}
}
