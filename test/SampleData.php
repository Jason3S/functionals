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

    static $complexSampleObject = null;
    static $complexSampleArray = null;

    static $pets = [
        [
            'name' => 'Tijgertje',
            'type' => 'cat',
            'breed' => 'tabby',
            'dob' => '2013-05-15',
            'address' => [
                'street' => 'Main',
                'number' => '1',
                'city'=>'Big City',
                'zip'=>'90210',
            ],
        ],
        [
            'name' => 'Rover',
            'type' => 'dog',
            'breed' => 'unknown',
            'dob' => '1967-05-15',
        ],
        [
            'name' => 'Garfield',
            'type' => 'cat',
            'breed' => 'tabby',
        ],
        [
            'name' => 'Odie',
            'type' => 'dog',
            'breed' => 'unknown',
        ],
        [
            'name' => 'Tramp',
            'type' => 'dog',
        ],
        [
            'name' => 'Lady',
            'type' => 'dog',
        ],
        [
            'name' => 'Lassy',
            'type' => 'dog',
        ],
        [
            'name' => 'Yellow Dog',
            'type' => 'dog',
        ],
        [
            'name' => 'Fluffy',
            'type' => 'dog',
        ],
        [
            'name' => 'Max',
            'type' => 'dog',
        ],
        [
            'name' => 'Tiger',
            'type' => 'cat',
        ],
        [
            'name' => 'Wolf',
            'type' => 'dog',
        ],
    ];


    public static function loadSamples() {
        if (empty(static::$complexSampleObject)) {
            $json = file_get_contents(__DIR__.'/sample.json');
            static::$complexSampleObject = json_decode($json);
            static::$complexSampleArray = json_decode($json, true);
        }
    }

    public static function getComplexSampleAsObject() {
        static::loadSamples();
        return static::$complexSampleObject;
    }

    public static function getComplexSampleAsArray() {
        static::loadSamples();
        return static::$complexSampleArray;
    }
}
