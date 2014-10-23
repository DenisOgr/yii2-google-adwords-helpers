<?php
namespace helpers;


use denisog\gah\helpers\Common;
use Codeception\Specify;

class CommonTest extends \Codeception\TestCase\Test
{
    use Specify;
    // tests
    public function testGoogleEncode()
    {
        $this->specify('should get valid keywords', function($url, $expectedKeyword){
        $actualKeyword = Common::googleEncode($url)['q'];
        expect($actualKeyword)->equals($expectedKeyword);
        },
            ['examples' =>[
                ['http://www.bing.com/search?q=January+2011+W9+&go=&qs=n&sk=&form=QBRE','January 2011 W9 '],
            ]]);
    }

}