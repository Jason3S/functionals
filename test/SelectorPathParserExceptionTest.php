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
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_FIELD_SEPARATOR,    SelectorPathParser::VALUE => '.'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'name'],
			],
            '(product|item).name' => [
                [SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_GROUP_BEGIN,        SelectorPathParser::VALUE => '('],
                [SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'product'],
                [SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_CONDITION_SEPARATOR,SelectorPathParser::VALUE => '|'],
                [SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'item'],
                [SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_GROUP_END,          SelectorPathParser::VALUE => ')'],
                [SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_FIELD_SEPARATOR,    SelectorPathParser::VALUE => '.'],
                [SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'name'],
            ],
			'products[type=55].name' => [
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'products'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_CONDITION_BEGIN,    SelectorPathParser::VALUE => '['],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'type'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_OPERATOR_COMPARISON,SelectorPathParser::VALUE => '='],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => '55'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_CONDITION_END,      SelectorPathParser::VALUE => ']'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_FIELD_SEPARATOR,    SelectorPathParser::VALUE => '.'],
				[SelectorPathParser::TOKEN => SelectorPathParser::TOKEN_WORD,               SelectorPathParser::VALUE => 'name'],
			],
		];

		foreach ($tests as $testPath => $expectedResult) {
			$this->assertEquals($expectedResult, SelectorPathParser::tokenize($testPath), $testPath);
		}

		// test out spacing and quote issues
		$this->assertEquals(SelectorPathParser::tokenize('products[type=55].name'), SelectorPathParser::tokenize('products[type = 55].name'));
		$this->assertEquals(SelectorPathParser::tokenize('products[type="55"].name'), SelectorPathParser::tokenize('products[type = 55].name'));
		$this->assertEquals(SelectorPathParser::tokenize('products[type="55"].name'), SelectorPathParser::tokenize('products[type = \'55\'].name'));
		$this->assertEquals(
			SelectorPathParser::tokenize('products[type=55&&price>"20.50"].name'),
			SelectorPathParser::tokenize('products [type = 55 && price > "20.50"].name'));
	}

    public function testParseTokens() {
        $tests = [
            'product'                          => true,
            '\'product\''                      => true,
            '"product"'                        => true,
            'product.name'                     => true,
            'product."product-name"'           => true,
            'product.product-name'             => false,  // -'s must be inside of quotes
            '(product)'                        => true,
            '(product).name'                   => true,
            '(product|item).name'              => true,
            '(teaches|students|staff).[].name' => true,
            '(teaches|students|staff)..name'   => true,
            'students.'                        => true,
            '.'                                => true,
            'products[type=55].name'           => true,
        ];

        foreach ($tests as $testPath => $isSuccessExpected) {
            $tokens = SelectorPathParser::tokenize($testPath);
            $parsedTokens = SelectorPathParser::processTokens($tokens);
            if ($isSuccessExpected) {
                $this->assertNotNull($parsedTokens, $testPath);
            } else {
                $this->assertNull($parsedTokens, $testPath);
            }
        }
    }
}
