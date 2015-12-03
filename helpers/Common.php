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
     * @param array $keys - for create associate array
     * @param array $settings - array [
     *      length - string length. Must be greater than the longest line (in characters)
     *           to be found in the CSV file
     *      delimiter - the optional delimiter parameter sets the field delimiter ('.', ',', "\t" etc.)
     *      removeFirst - remove ... lines from the beginning of the file
     *      removeLast - remove ... lines from the end of the file
     *      numberHeader - revert return array keys to integer numbers
     * ]
     * @return array
     * @throws Exception
     */
    public static function csvToArray($fileToPath, array $keys = [],  array $settings =[]){
       //Default

        if (!file_exists($fileToPath)) {
            throw new \Exception('Not exist csv file', 500);
        }
        $result = [];

        $defParams = [
            'length' => 0,
            'delimiter' => ',',
            'removeLast' => 0,
            'removeFirst' => 0,
            'numberHeader' => false
        ];

        $settings = (!empty($settings)) ? self::arrayExtends($settings, $defParams) : $defParams;

        $file = fopen($fileToPath, 'r');
        while (($line = fgetcsv($file, $settings['length'], $settings['delimiter'])) !== FALSE) {
            $result[] = $line;
        }
        fclose($file);

        //removing last
        if (isset($settings['removeLast'])) {
            for ($i = (count($result) - $settings['removeLast']); $i <= count($result); $i++) {
                unset($result[$i]);
            }
        }

        //removing first
        if (isset($settings['removeFirst'])) {
            for ($i = 0; $i < $settings['removeFirst']; $i++) {
                unset($result[$i]);
            }
        }

        reset($result);
        $firstKey = key($result);

        if(isset($settings['numberHeader']) && $settings['numberHeader']) {
            $keys = range(0, count($firstKey));
        }

         //get keys
        if (empty($keys)) {
            $keys = $result[$firstKey];
        }

        //isset header
        if(!isset($settings['numberHeader'])) {
            unset($result[$firstKey]);
        }

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


    public static function searchEncode($url)
    {
        $result = parse_url($url);

        if(isset($result['query'])) {
            parse_str($result['query'],$result);
            return $result;
        }
        return null;
    }

    public static function getKeyword($url)
    {
        $keyword = null;
        $params = self::searchEncode($url);

        if (isset($params['q'])) {
            $keyword = $params['q'];
        } elseif(isset($params['p'])) {
            $keyword = $params['p'];
        }
        return $keyword;
    }
    /**
     * Get params from google url
     * @param $w
     * @return array
     */
    public static function googleEncode($w){
        $w=substr($w,strpos($w,'search?')+7);
        $ar=explode('&',str_replace('?q=','&q=',$w));
        $q='';
        $cd='';
        for($i=0;$i<count($ar);$i++){
            $pos=strpos($ar[$i],'=');
            $str=substr($ar[$i],0,$pos);
            if($str=='q'){
                $q=substr($ar[$i],$pos+1);
            }elseif($str=='cd'){
                $cd=substr($ar[$i],$pos+1);
            }
        }
        return array('q'=>urldecode($q),'cd'=>$cd);
    }

    /**
     * Get params from yahoo url
     * @param $w
     * @return array
     */
    public static function yahooEncode($w){
        $w=substr($w,strpos($w,'search?')+7);
        $ar=explode('&',str_replace('?p=','&p=',$w));
        $q='';
        $cd='';
        for($i=0;$i<count($ar);$i++){
            $pos=strpos($ar[$i],'=');
            $str=substr($ar[$i],0,$pos);
            if($str=='p'){
                $q=substr($ar[$i],$pos+1);
            }elseif($str=='cd'){
                $cd=substr($ar[$i],$pos+1);
            }
        }
        return array('p'=>urldecode($q),'cd'=>$cd);
    }

    /**
     * Convert characters to normal
     * @param $string
     * @return string
     */
    public static function convertAccentsAndSpecialToNormal($string) {
        $table = [
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Ă'=>'A', 'Ā'=>'A', 'Ą'=>'A', 'Æ'=>'A', 'Ǽ'=>'A',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'ă'=>'a', 'ā'=>'a', 'ą'=>'a', 'æ'=>'a', 'ǽ'=>'a',

            'Þ'=>'B', 'þ'=>'b', 'ß'=>'Ss',

            'Ç'=>'C', 'Č'=>'C', 'Ć'=>'C', 'Ĉ'=>'C', 'Ċ'=>'C',
            'ç'=>'c', 'č'=>'c', 'ć'=>'c', 'ĉ'=>'c', 'ċ'=>'c',

            'Đ'=>'Dj', 'Ď'=>'D', 'Đ'=>'D',
            'đ'=>'dj', 'ď'=>'d',

            'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ĕ'=>'E', 'Ē'=>'E', 'Ę'=>'E', 'Ė'=>'E',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ĕ'=>'e', 'ē'=>'e', 'ę'=>'e', 'ė'=>'e',

            'Ĝ'=>'G', 'Ğ'=>'G', 'Ġ'=>'G', 'Ģ'=>'G',
            'ĝ'=>'g', 'ğ'=>'g', 'ġ'=>'g', 'ģ'=>'g',

            'Ĥ'=>'H', 'Ħ'=>'H',
            'ĥ'=>'h', 'ħ'=>'h',

            'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'İ'=>'I', 'Ĩ'=>'I', 'Ī'=>'I', 'Ĭ'=>'I', 'Į'=>'I',
            'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'į'=>'i', 'ĩ'=>'i', 'ī'=>'i', 'ĭ'=>'i', 'ı'=>'i',

            'Ĵ'=>'J',
            'ĵ'=>'j',

            'Ķ'=>'K',
            'ķ'=>'k', 'ĸ'=>'k',

            'Ĺ'=>'L', 'Ļ'=>'L', 'Ľ'=>'L', 'Ŀ'=>'L', 'Ł'=>'L',
            'ĺ'=>'l', 'ļ'=>'l', 'ľ'=>'l', 'ŀ'=>'l', 'ł'=>'l',

            'Ñ'=>'N', 'Ń'=>'N', 'Ň'=>'N', 'Ņ'=>'N', 'Ŋ'=>'N',
            'ñ'=>'n', 'ń'=>'n', 'ň'=>'n', 'ņ'=>'n', 'ŋ'=>'n', 'ŉ'=>'n',

            'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ō'=>'O', 'Ŏ'=>'O', 'Ő'=>'O', 'Œ'=>'O',
            'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ō'=>'o', 'ŏ'=>'o', 'ő'=>'o', 'œ'=>'o', 'ð'=>'o',

            'Ŕ'=>'R', 'Ř'=>'R',
            'ŕ'=>'r', 'ř'=>'r', 'ŗ'=>'r',

            'Š'=>'S', 'Ŝ'=>'S', 'Ś'=>'S', 'Ş'=>'S',
            'š'=>'s', 'ŝ'=>'s', 'ś'=>'s', 'ş'=>'s',

            'Ŧ'=>'T', 'Ţ'=>'T', 'Ť'=>'T',
            'ŧ'=>'t', 'ţ'=>'t', 'ť'=>'t',

            'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ũ'=>'U', 'Ū'=>'U', 'Ŭ'=>'U', 'Ů'=>'U', 'Ű'=>'U', 'Ų'=>'U',
            'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ũ'=>'u', 'ū'=>'u', 'ŭ'=>'u', 'ů'=>'u', 'ű'=>'u', 'ų'=>'u',

            'Ŵ'=>'W', 'Ẁ'=>'W', 'Ẃ'=>'W', 'Ẅ'=>'W',
            'ŵ'=>'w', 'ẁ'=>'w', 'ẃ'=>'w', 'ẅ'=>'w',

            'Ý'=>'Y', 'Ÿ'=>'Y', 'Ŷ'=>'Y',
            'ý'=>'y', 'ÿ'=>'y', 'ŷ'=>'y',

            'Ž'=>'Z', 'Ź'=>'Z', 'Ż'=>'Z', 'Ž'=>'Z',
            'ž'=>'z', 'ź'=>'z', 'ż'=>'z', 'ž'=>'z',

            '“'=>'"', '”'=>'"', '‘'=>"'", '’'=>"'", '•'=>'-', '…'=>'...', '—'=>'-', '–'=>'-', '¿'=>'?', '¡'=>'!', '°'=>' degrees ',
            '¼'=>' 1/4 ', '½'=>' 1/2 ', '¾'=>' 3/4 ', '⅓'=>' 1/3 ', '⅔'=>' 2/3 ', '⅛'=>' 1/8 ', '⅜'=>' 3/8 ', '⅝'=>' 5/8 ', '⅞'=>' 7/8 ',
            '÷'=>' divided by ', '×'=>' times ', '±'=>' plus-minus ', '√'=>' square root ', '∞'=>' infinity ',
            '≈'=>' almost equal to ', '≠'=>' not equal to ', '≡'=>' identical to ', '≤'=>' less than or equal to ', '≥'=>' greater than or equal to ',
            '←'=>' left ', '→'=>' right ', '↑'=>' up ', '↓'=>' down ', '↔'=>' left and right ', '↕'=>' up and down ',
            '℅'=>' care of ', '℮' => ' estimated ',
            'Ω'=>' ohm ',
            '♀'=>' female ', '♂'=>' male ',
            '©'=>' Copyright ', '®'=>' Registered ', '™' =>' Trademark ',
        ];

        $string = strtr($string, $table);
        $string = preg_replace('/[^A-Za-z0-9+_ !#$%^&*\)\(\:;-]/', '', $string);
        $string = trim(preg_replace('/ {2,}/', ' ', $string));

        return $string;
    }

    /**
     * Init default settings
     *
     * @param array $data: your array params
     * @param array $defautData: default array params
     * @return array
     */
    public static function arrayExtends($data, $defautData) {
        $result = [];
        foreach($defautData as $key => $val) {
            $result[$key] = isset($data[$key]) ? $data[$key] : $val;
        }
        return $result;
    }
}
