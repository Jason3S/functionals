<?php
/**
 * Created by PhpStorm.
 * User: jasondent
 * Date: 07/04/15
 * Time: 14:12
 */

namespace functionals;

require_once 'SampleData.php';

use functionals\test\SampleData as SampleData;

class SelectorsTest extends \PHPUnit_Framework_TestCase {

    public function testSelect() {
        $a = Selector::make(SampleData::$sampleData, '.')->toArray();
        $b = Sequence::make(SampleData::$sampleData)->select('.')->toArray();
        $this->assertEquals($a, $b);
        $this->assertNotNull($a);

        $a = Sequence::make(SampleData::$sampleData)->select('..')->toArray();
        $b = Sequence::make(SampleData::$sampleData)->select('.')->select('.')->toArray();
        $this->assertEquals($a, $b);
        $this->assertNotNull($a);

        $c = Sequence::make(SampleData::$sampleData)->select('.products..price_options')->toArray();
        $d = Sequence::make(SampleData::$sampleData)->select('...price_options')->toArray();
        $this->assertEquals($c, $d);
        $e = Sequence::make(SampleData::$sampleData)->select('...price_options.')->toArray();
        $f = Sequence::make(SampleData::$sampleData)->select('...price_options.[]')->toArray();
        $this->assertEquals($d['price_options'], $e);
        $this->assertNotEquals($e, $f);
        $g = Sequence::make($e)->filter(fnNotEmpty())->toArray();
        $this->assertEquals($g, $f);

    }

    public function testSelectConditions() {
        $f = Sequence::make(SampleData::$sampleData)->select('...price_options.[]')->toArray();
        $e = Sequence::make(SampleData::$sampleData)->select('...price_options.')->toArray();
        $g = Sequence::make($e)->filter(fnNotEmpty())->toArray();
        $this->assertEquals($g, $f);
    }

    public function testSelectors() {
        $pet = SampleData::$pets[0];

        $f = Sequence::make($pet)->select('name')->toArray();
        $this->assertEquals(['name' => $pet['name']], $f);

        $f = Sequence::make($pet)->select('dob')->toArray();
        $this->assertEquals(['dob' => $pet['dob']], $f);

        $f = Sequence::make($pet)->select('name|dob')->toArray();
        $this->assertEquals([
            'name' => $pet['name'],
            'dob'  => $pet['dob']
        ], $f);
    }

    public function testLargerSample() {
        $sampleObj = SampleData::getComplexSampleAsObject();
        $sampleArr = SampleData::getComplexSampleAsArray();

        $samples = array(
            $sampleObj, $sampleArr
        );

        $pathsExists = array(
            '.word'                      => true,
            '.dataFound'                 => true,
            '.wordSets..entries.'        => true,
            '.wordSets..entries.srcWord' => false,  // Should be empty because entries is an array
        );

        $pathsEquals = array(
            '.word', '.dataFound',
            '.wordSets..entries.srcWord',  // Should be empty because entries is an array
            '.wordSets..entries..srcWord', // array of words.
        );


        // Make sure they match each other.
        foreach ($pathsEquals as $path) {
            $a = Sequence::make($sampleObj)->select($path)->values()->toArray();
            $b = Sequence::make($sampleArr)->select($path)->values()->toArray();
            $this->assertEquals($a, $b);

            $a = Sequence::make($sampleObj)->select($path)->keys()->toArray();
            $b = Sequence::make($sampleArr)->select($path)->keys()->toArray();
            $this->assertEquals($a, $b);
        }

        // Make sure they are not null.
        foreach ($samples as $sample) {
            foreach ($pathsExists as $path => $hasData) {
                $a = Sequence::make($sample)->select($path)->toArray();
                if ($hasData) {
                    $this->assertNotEmpty($a);
                } else {
                    $this->assertEmpty($a);
                }
            }
        }

        // spot check.
        $path = '.wordSets..entries.';
        $a    = Sequence::make($sampleObj)->select($path);
        $b    = Sequence::make($sampleArr)->select($path);
        $path = 'srcWord';
        $a    = Sequence::make($a)->select($path)->pairKeyValues()->toArray();
        $b    = Sequence::make($b)->select($path)->pairKeyValues()->toArray();
        $this->assertEquals($a, $b);

        $path = '.wordSets..entries.';
        $a    = Sequence::make($sampleObj)->select($path)->toArray();
        $b    = Sequence::make($sampleArr)->select($path)->toArray();
        $path = 'srcWord|srcCodes';
        $a    = Sequence::make($a)->select($path)->pairKeyValues()->toArray();
        $b    = Sequence::make($b)->select($path)->pairKeyValues()->toArray();
        $this->assertEquals($a, $b);
    }

    public function testCondition() {
        $pets = SampleData::$pets;

        $a = Sequence::make($pets)->select('[dob]')->toArray();
        $this->assertNotEmpty($a);

        $a = Sequence::make($pets)->select('[address].name')->values()->toArray();
        $this->assertNotEmpty($a);
        $this->assertTrue(in_array('Tijgertje', $a));

        $a = Sequence::make($pets)->select('[dob]')->toArray();
        $b = Sequence::make($pets)->select('[name,dob]')->values()->toArray();
        $this->assertEquals($a, $b);
        $c = Sequence::make($pets)->select('[name]')->values()->toArray();
        $this->assertNotEquals($a, $c);
        $d = Sequence::make($pets)->select('[address]')->values()->toArray();
        $e = Sequence::make($pets)->select('[name & address]')->values()->toArray();
        $this->assertEquals($d, $e);

        $a = Sequence::make($pets)->select('[dob]')->limit(1)->toArray();
        $b = Sequence::make($pets)->select('[name=Tijgertje]')->toArray();
        $this->assertEquals($a, $b);

        $c = Sequence::make($pets)->select('[name > "Tij" & name < Tj]')->toArray();
        $this->assertEquals($a, $c);

        $d = Sequence::make($pets)->select('[name > "O" & name < "T"]')->toArray();
        $e = Sequence::make($pets)->select('[name = "Odie" | name="Rover" ]')->toArray();
        $this->assertEquals($d, $e);
    }

    public function testConditionRegEx() {
        $pets = SampleData::$pets;

        $a = Sequence::make($pets)->select('[name=Tijgertje]')->toArray();
        $b = Sequence::make($pets)->select('[name=/tijgertje/i]')->toArray();
        $this->assertEquals($a, $b);

        $b = Sequence::make($pets)->select('[name=/tje/i]')->toArray();
        $this->assertEquals($a, $b);

        $a = Sequence::make($pets)->select('[type=dog & name=/^[rtw]/i].name')->values()->toArray();
        $b = Sequence::make($pets)->select('[type=dog & name=/[rtw]/iA].name')->values()->toArray();
        $this->assertContains('Wolf', $a);
        $this->assertEquals($a, $b);
    }

    public function posFromCode($code) {
        $posExp = [
            '~1236~' => 'noun',
            '~5~' => 'verb',
            '~(7.*A)|(A.*7)~' => 'adjective',
            '~7~' => 'modifier',
        ];

        foreach ($posExp as $exp => $pos) {
            if (preg_match($exp, $code)) {
                return $pos;
            }
        }

        return '';
    }

    public function testLargeSample() {

        // Changing the map values with objects WILL change the original data set.
        $dictEntry = SampleData::getComplexSampleAsObject();
        $a = Sequence::make($dictEntry)
            ->select('.wordSets..entries.')
            ->map(function($value) {
                $value->pos = $this->posFromCode($value->srcCodes);
                return $value;
            })
            ->toArray();

        $b = Sequence::make($dictEntry)
            ->select('.wordSets..entries.')
            ->toArray();

        $this->assertEquals($b, $a);

        // Changing the map value with arrays will not change the original data set.
        $dictEntry = SampleData::getComplexSampleAsArray();
        $a = Sequence::make($dictEntry)
                     ->select('.wordSets..entries.')
                     ->map(function($value) {
                         $value['pos'] = $this->posFromCode($value['srcCodes']);
                         return $value;
                     })
                     ->toArray();

        $b = Sequence::make($dictEntry)
                     ->select('.wordSets..entries.')
                     ->toArray();

        $this->assertNotEquals($b, $a);

        // Changing the map value with arrays will not change the original data set.
        $dictEntry = SampleData::getComplexSampleAsArray();

        $a = Sequence::make($dictEntry)
                     ->select('.wordSets..entries.')
                     ->toArray();

        Sequence::make($dictEntry)
                     ->select('.wordSets..entries.')
                     ->walk(function(&$value) {
                         $value['pos'] = $this->posFromCode($value['srcCodes']);
                     });

        $b = Sequence::make($dictEntry)
                     ->select('.wordSets..entries.')
                     ->toArray();

        $this->assertEquals($a, $b);
    }

    public function testSelectAndApplyMap() {
        $dictEntry = SampleData::getComplexSampleAsArray();
        $definitions = Sequence::make($dictEntry)->select('.wordSets..defs.')->keyBy(fnExtract('defnr'))->toArray();
        $a = Sequence::make($dictEntry)
             ->selectAndMap('wordSets..entries.[srcCodes=/1|2|3|6/]', function ($value, $key, $parent, $parents) {
                 $value['pos'] = 'noun';
                 return $value;
             })
            ->selectAndMap('wordSets..entries.[srcCodes=/5/]', function ($value, $key, $parent, $parents) {
                $value['pos'] = 'verb';
                return $value;
            })
            ->selectAndMap('wordSets..entries.[defnr>0]', function ($value, $key, $parent, $parents) use ($definitions) {
                $value['def'] = \functionals\extractValue($definitions, $value['defnr']);
                return $value;
            })
            ->toArray();

        $verbs = Sequence::make($a)->select('.wordSets..entries.[pos=verb]')->toArray();
        $this->assertNotEmpty($verbs);

        $nouns = Sequence::make($a)->select('.wordSets..entries.[pos=noun]')->toArray();
        $nouns2 = Sequence::make($a)->select('.wordSets..entries.[srcCodes=/1|2|3|6/]')->toArray();
        $this->assertNotEmpty($nouns);
        $this->assertEquals($nouns2, $nouns);
    }
}