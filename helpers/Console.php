<?php
/**
 * User: Denis Porplenko <denis.porplenko@gmail.com>
 * Date: 14.08.14
 * Time: 9:43
 */

namespace denisog\gah\helpers;

/**
 * Helpers work with console
 * Class Console
 * @package vendor\denisogr\gah\helpers
 */
class Console {

    /**
     * Print some info
     * @param $text
     */
    public static function prn($text)
    {
        print_r($text . "\n");
    }
} 