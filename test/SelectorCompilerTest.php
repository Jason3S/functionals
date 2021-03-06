<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 08/04/15
 * Time: 09:09 AM
 */

namespace functionals;

class SelectorCompilerTest extends \PHPUnit_Framework_TestCase {
	public function testTokenizer() {
		$tests = [
			'product' => [
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD, SelectorCompiler::VALUE => 'product']
			],
			'\'product\'' => [
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD, SelectorCompiler::VALUE => 'product']
			],
			'"product"' => [
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD, SelectorCompiler::VALUE => 'product']
			],
			'product.name' => [
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'product'],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_FIELD_SEPARATOR,    SelectorCompiler::VALUE => '.'],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'name'],
			],
            '...price_options.[]' => [
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_FIELD_SEPARATOR,    SelectorCompiler::VALUE => '.'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_FIELD_SEPARATOR,    SelectorCompiler::VALUE => '.'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_FIELD_SEPARATOR,    SelectorCompiler::VALUE => '.'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'price_options'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_FIELD_SEPARATOR,    SelectorCompiler::VALUE => '.'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_CONDITION_BEGIN,    SelectorCompiler::VALUE => '['],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_CONDITION_END,      SelectorCompiler::VALUE => ']'],
            ],
            '(product|item).name' => [
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_GROUP_BEGIN,        SelectorCompiler::VALUE => '('],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'product'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_CONDITION_SEPARATOR,SelectorCompiler::VALUE => '|'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'item'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_GROUP_END,          SelectorCompiler::VALUE => ')'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_FIELD_SEPARATOR,    SelectorCompiler::VALUE => '.'],
                [SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'name'],
            ],
			'products[type=55].name' => [
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'products'],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_CONDITION_BEGIN,    SelectorCompiler::VALUE => '['],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'type'],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_OP_EQUAL,           SelectorCompiler::VALUE => '='],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => '55'],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_CONDITION_END,      SelectorCompiler::VALUE => ']'],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_FIELD_SEPARATOR,    SelectorCompiler::VALUE => '.'],
				[SelectorCompiler::TOKEN => SelectorCompiler::TOKEN_WORD,               SelectorCompiler::VALUE => 'name'],
			],
		];

		foreach ($tests as $testPath => $expectedResult) {
			$this->assertEquals($expectedResult, SelectorCompiler::tokenize($testPath), $testPath);
		}

		// test out spacing and quote issues
		$this->assertEquals(SelectorCompiler::tokenize('products[type=55].name'), SelectorCompiler::tokenize('products[type = 55].name'));
		$this->assertEquals(SelectorCompiler::tokenize('products[type="55"].name'), SelectorCompiler::tokenize('products[type = 55].name'));
		$this->assertEquals(SelectorCompiler::tokenize('products[type="55"].name'), SelectorCompiler::tokenize('products[type = \'55\'].name'));
		$this->assertEquals(
			SelectorCompiler::tokenize('products[type=55&&price>"20.50"].name'),
			SelectorCompiler::tokenize('products [type = 55 && price > "20.50"].name'));
        $this->assertNotEquals(SelectorCompiler::tokenize(','), SelectorCompiler::tokenize('.'));
	}

    public function testProcessTokens() {
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
            'products[type="55"].name'         => true,
            'products[type=/55/A&type>40].name' => true,
            'products[type==55].name'          => true,
            'products[type!=55].name'          => true,
            'products[type!==55].name'         => true,
            'products[type===55].name'         => false,
            'products[type==/55].name'         => false,
        ];

        foreach ($tests as $testPath => $isSuccessExpected) {
            try {
                $tokens = SelectorCompiler::tokenize($testPath);
                $parsedTokens = SelectorCompiler::processTokens($tokens);
            } catch (SelectorCompilerException $e) {
                $parsedTokens = null;
            }
            if ($isSuccessExpected) {
                $this->assertNotNull($parsedTokens, $testPath);
            } else {
                $this->assertNull($parsedTokens, $testPath);
            }
        }
    }

}
