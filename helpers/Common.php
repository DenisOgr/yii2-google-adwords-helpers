<?php
/**
 * User: Denis Porplenko <denis.porplenko@gmail.com>
 * Date: 20.08.14
 * Time: 14:45
 */

namespace denisog\gah\helpers;


class Common {

    /**Get first item in array
     * @param array $results
     * @return bool|mixed
     */
    static function getFirstRowFromResults(array $results){

        if($results){

            if(isset($results->entries[0]))
                return $results->entries[0];

            if(isset($results->value[0]))
                return $results->value[0];

        }

        return false;
    }

    /**
     * Change spaces to plus(+)
     * @param string $keyword
     * @return string
     */
    static function convertSpaceToPlus
    ($keyword){

        $data = explode(' ', $keyword);

        return '+' . implode(' +', $data);

    }
} 