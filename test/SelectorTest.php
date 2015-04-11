<?php
/**
 * Created by PhpStorm.
 * User: jasondent
 * Date: 07/04/15
 * Time: 14:12
 */

namespace functionals\test;


use functionals\Selector\Selector;
use functionals\Sequence;
use functionals\FnGen as fn;

class SelectorsTest extends \PHPUnit_Framework_TestCase {
    static $sampleData = [
        [
            'company' => [
                'name' => 'Data Corp',
                'address' => [
                    'street' => '1 Broadway',
                    'city'=>'San Francisco',
                    'state'=>'CA',
                    'country'=>'USA',
                ],
            ],
            'products' => [
                [
                    'name' => 'Zip Codes By State',
                    'size' => 3029453,
                    'price_options' => [
                        [
                            'name' => 'One Time Purchase',
                            'price' => 59.99,
                            'subscription' => false,
                            'notes' => 'Single Download',
                        ],
                        [
                            'name' => 'Monthly Updates',
                            'price' => 22.50,
                            'billing_period' => 'month',
                            'update_freq' => 'month',
                            'minimum_subscription' => '3 months',
                            'subscription' => true,
                        ],
                        [
                            'name' => 'Weekly Updates',
                            'price' => 25.50,
                            'billing_period' => 'month',
                            'update_freq' => 'week',
                            'minimum_subscription' => '3 months',
                            'subscription' => true,
                        ],
                        [
                            'name' => 'Daily Updates',
                            'price' => 27.50,
                            'billing_period' => 'month',
                            'update_freq' => 'day',
                            'minimum_subscription' => '3 months',
                            'subscription' => true,
                        ],
                        [],
                    ],
                    'id' => 2848292,
                    'extra' => [
                    ]
                ],
            ]
        ],
    ];

    public function testConditionalSelect() {
        // [field=value]
        // [field>value]
        // Select($iterator, 'products[].price_options[price>25]')
        // 'products[].*[update_freq=week]'
    }

    public function testSelector() {
        $a = Selector::make(static::$sampleData, '.')->toArray();
        $b = Sequence::make(static::$sampleData)->select('.')->toArray();
        $this->assertEquals($a, $b);
        $this->assertNotNull($a);

        $a = Sequence::make(static::$sampleData)->select('..')->toArray();
        $b = Sequence::make(static::$sampleData)->select('.')->select('.')->toArray();
        $this->assertEquals($a, $b);
        $this->assertNotNull($a);

        $c = Sequence::make(static::$sampleData)->select('.products..price_options')->toArray();
        $d = Sequence::make(static::$sampleData)->select('...price_options')->toArray();
        $e = Sequence::make(static::$sampleData)->select('...price_options.')->toArray();
        $f = Sequence::make(static::$sampleData)->select('...price_options.[]')->toArray();
        $this->assertEquals($c, $d);
        $this->assertEquals($d['price_options'], $e);
        $this->assertNotEquals($e, $f);
        $g = Sequence::make($e)->filter(fn\fnNotEmpty())->toArray();
        $this->assertEquals($g, $f);

        $d = Sequence::make(static::$sampleData)->select('.')->offset(1)->select('..[]')->toArray();

        $a = $c;

	    // products[].price_options[]

        /*
        $selector = Selector::make(static::$sampleData)
            ->selectField('products')
	        ->selectChildren()
            ->selectField('price_options')
            ->selectChildren();


        $values = $selector->toArray();

        $this->assertEquals(static::$sampleData[0]['products'][0]['price_options'], $values);

	    // products[].price_options[].name

	    $selector = Selector::make(static::$sampleData)
	        ->selectField('products')
	        ->selectChildren()
	        ->selectField('price_options')
	        ->selectChildren()
	        ->selectField('name');


	    $values = $selector->toArray();

	    $this->assertEquals(Sequence::make(static::$sampleData[0]['products'][0]['price_options'])->map(\functionals\FnGen\fnExtract('name'))->toArray(), $values);

        */
    }

}
