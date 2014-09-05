<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 8/27/14
 * Time: 5:39 PM
 */

class TestPhrases2 extends PHPUnit_Framework_TestCase{

    public static $result = [];
    public static $fileName;

    /**
     * @dataProvider provider
     * @param $input
     */
    public function testRun($input)
    {
        $actualPhrasesR = \denisog\gah\helpers\PhrasesR::run($input);
        $actualPhrases  = \denisog\gah\helpers\PhrasesOrig::GetManualPhrases($input);

        $messageError   = "Error Input: {$input}";
       // $this->assertEqualsArrays($actualPhrasesR, $actualPhrases, $messageError);
        self::$result[] = [
            'string' => $input,
            'new_parser' => $actualPhrasesR,
            'old_parser' => $actualPhrases,
            'same' => array_intersect($actualPhrasesR, $actualPhrases),
            'only_in_old' => array_diff($actualPhrases, $actualPhrasesR),
            'only_in_new' => array_diff($actualPhrasesR, $actualPhrases),
        ];

    }

    public function provider()
    {
        $result[] = ["2012 army form ({[<DA><5118>]}<5118>) reassignment status election statement"];
        $result[] = ["'2008' IRS form 8821 tax (<info><information>) authorization"];
        $result[] = ["97 (<CA><california>) participating (<app><application>) form"];
        $result[] = ["(<CA><California>) participating physician (<reapplication><reapp><re-app>) form"];
        $result[] = ["2012 IRS 'instruction' form ({[<i1099><div>]}{[<1099><DIV>]})"];

        return $result;

    }

    protected function assertEqualsArrays($expected, $actual, $message = 'Error not equal') {

        $this->assertEquals(0, count(array_diff_assoc($expected, $actual)));
        sort($expected, SORT_NATURAL);
        sort($actual, SORT_NATURAL);

        $this->assertEquals($expected, $actual, $message);
    }
    public static function tearDownAfterClass()
    {
        self::$fileName = FIXTURES . '/result_with_old.json';
        @unlink(self::$fileName);
        file_put_contents(self::$fileName, json_encode(self::$result));
    }

}