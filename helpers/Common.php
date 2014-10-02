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

    /**
     * @param $fileToPath
     * @param array $keys
     * @param array $settings
     * @return array
     * @throws Exception
     */
    public static function csvToArray($fileToPath, array $keys = [],  array $settings =[]){
       //Default

        if (!file_exists($fileToPath)) {
            throw new Exception('Not exist csv file', 500);
        }
        $result = [];
        $file = fopen($fileToPath, 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            $result[] = $line;
        }
        fclose($file);

        //removing last
        if (isset($settings['removeLast'])) {
            unset($result[count($result)-1]);
        }

        //removing first
        if (isset($settings['removeFirst'])) {
            unset($result[0]);
        }
        //get keys
        if (empty($keys)) {
            $keys = $result[1];
        }
        unset($result[1]);

        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $newResult[] = self::renameKeys($value, $keys);
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

        for ($i=0; $i < count($keys); $i++) {
            if(!empty($replacement_keys[$i])) {
                $keys[$i] =  $replacement_keys[$i];

            }
        }

        return array_combine($keys, $values);
    }

    /**
     * Return uniq ID for keyword
     * @param string $keywordId
     * @param string $groupId
     * @return string
     */
    public static function getKeywordGroup($keywordId, $groupId)
    {
        return "{$keywordId}-{$groupId}";
    }
} 