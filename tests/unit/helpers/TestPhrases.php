<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 8/27/14
 * Time: 5:39 PM
 */

class TestPhrases extends PHPUnit_Framework_TestCase{

    public static $result = [];
    public static $fileName;



    public static function setUpBeforeClass()
    {
        self::$result = [];
    }
    /**
     * @dataProvider provider
     */
    public function testRun($input, array $expected)
    {
        $actual = \denisog\gah\helpers\PhrasesR::run($input);
        $this->assertTrue(is_array($actual));
        $messageError = "Error Input: {$input}";
        //$this->assertEqualsArrays($actual, $expected, $messageError);
        self::$result[] = [
            'string' => $input,
            'expected' => $expected,
            'same' => array_intersect($actual, $expected),
            'only_in_actual' => array_diff($actual, $expected),
            'only_in_expected' => array_diff($expected, $actual),
        ];


    }

    public function provider()
    {
        foreach (json_decode(file_get_contents(FIXTURES . '/test.JSON'),true) as $fixture) {
            $result[] = [$fixture['source'],$fixture['result']];
        }
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
        self::$fileName = FIXTURES . '/result.json';
        @unlink(self::$fileName);
        file_put_contents(self::$fileName, json_encode(self::$result));
    }
}