<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 11/04/15
 * Time: 12:21 PM
 */

namespace functionals\test;


class SampleData {
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

}
