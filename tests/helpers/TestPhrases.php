<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 8/27/14
 * Time: 5:39 PM
 */
require_once(dirname(__FILE__) . '/../../../../autoload.php');

class TestPhrases extends PHPUnit_Framework_TestCase{

    public function testA()
    {
        \denisog\gah\helpers\Phrases::GetManualPhrases("({How to}{How do I}{Where to}{Where do I}) (<search><get><find>) (<fillable><typeable><editable><printable>) 'Core' '(<application><documents><petition><request><form>)' (<pdf><doc><edit.doc>) (<fill><online><print><email><fax><sign><download><share><pdffiller>)");

    }
}