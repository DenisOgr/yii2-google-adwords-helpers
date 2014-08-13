<?php
/**
 * User: Denis Porplenko <denis.porplenko@gmail.com>
 * Date: 13.08.14
 * Time: 11:38
 */

namespace gah\helpers;

class Reports {

    /**
     * @param string $fileToPath  path to file
     * @param array $fields array with needly fields in output
     * @return array -  result array with $fields
     * @throws \Exception - when can not find file
     */
    public static function csvToArrayFromFileWithoutInfo($fileToPath, array $fields){
        if (!file_exists($fileToPath)) {
            throw new \Exception('Not exist csv file', 500);
        }
        $result = array_map('str_getcsv', file($fileToPath));

        //remove info
        unset($result[count($result)-1], $result[0],$result[1]);
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $newResult[] = Rating::renameKeys($value, $fields);
            }
            $result = $newResult;
        }
        return $result;
    }

    /**
     * Replaces the keys in the given array with an array of in-order
     * replacement keys.
     *
     * @param array &$array
     * @param array $replacement_keys
     **/
    public static function renameKeys($array, $replacement_keys)
    {
        $keys   = array_keys($array);
        $values = array_values($array);

        for ($i=0; $i < count($replacement_keys); $i++) {
            $keys[$i] = $replacement_keys[$i];
        }

        return  array_combine($keys, $values);
    }
} 