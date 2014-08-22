<?php

/**
 * @author Konstantin Shevchenko <koshevchenko@gmail.com>
 */
namespace denisog\gah\helpers;

class Phrases{

    /**
     *
     * @param $ph1 - слово
     * @param $words - массив слов
     * @param $i
     */
    public static function phrase($ph1, $words, $i){

        global $all_ph; //массив со всеми фразами,
        global $all_bad_ph;
        //запускаю цикл, который проходит по массиву со всеми словами,
        //но начинает проход с индекса(ключа) $i
        for($j = $i; $j < count($words); $j++){
            //конкатенирую слово из массива с текущем, через пробел
            $ph = $ph1.' '.$words[$j];
            //если текущего слова нет в массив со всеми фразами и если его размер меньше 80 символов,
            //то добавляю  фразу в массив со всеми фразами
            if(!in_array($ph, $all_ph)){
                if(strlen($ph) >= 80){
                    $all_bad_ph[] = $ph;
                }else{
                    $all_ph[] = $ph;
                }
                //если это не последнее слово, то забускаю функцию формирования фразы
                //устанавливаю ключ = текущий + 1
                if($j < count($words) - 1){
                    Phrases::phrase($ph, $words, $j+1);
                }
            }
        }
    }

    public static function GetCountManualPhrases(){
        global $all_ph;
        $c = count($all_ph);
        return $c;
    }

    public static function GetCountBadPhrases(){
        global $all_bad_ph;
        $c = count($all_bad_ph);
        return $c;
    }

    public static function GetCountValidPhrases(){
        global $phrases;
        $c = count($phrases);
        return $c;
    }

    public static function GetManualPhrases($manual_phrase=''){

       global $phrases;

        $words_arr = array();
        //левый кавычки
        $brackets1 = array('[', '{', '(', '<', "'", '"');
        //правые  кавычки
        $brackets2 = array(']', '}', ')', '>', "'", '"');
        //вырезаю угловые кавычки из входящей строки
        $manual_phrase = str_replace(array('<', '>'), ' ', $manual_phrase);
        //заменяю все пробелы больше одного на один пробел
        $manual_phrase = trim(preg_replace('/[\s]+/', ' ', $manual_phrase));
        //получается строка, в которой есть только круглые и фигурные скобки, одинарные кавычки  и пробелы.
        //все углобые кавычки были заменены на пробелы
        $manual_phrase = Phrases::GetUpdatePhrase($manual_phrase);
        //массив данных по каждой подстроки
        $words_arr = Phrases::GetWordsArray($manual_phrase, $brackets1, $brackets2);


        //строка ьез каких либо знаков препинания. только слова разделенные пробелами
        $param = str_replace($brackets1, ' ', $manual_phrase);
        $param = str_replace($brackets2, ' ', $param);
        $param = trim(preg_replace('/[\s]+/', ' ', $param));

        //массив слов
        $param_array = explode(' ', $param);

        $phrases = Phrases::GetPhrase_no_google3($param_array, $words_arr, $brackets1, $brackets2);
        $phrases = Phrases::DeleteNotValidPhrases($phrases);

        //sort($phrases);
        usort($phrases, ['denisog\gah\helpers\Phrases', 'cmp']);
        $phrases = array_slice($phrases, 0, 10000);

        return $phrases;
    }

    public static function cmp($a, $b) {
        if (strlen($a) > strlen($b)){
            return 1;
        }else if (strlen($a) == strlen($b)){
            return 0;
        }else{
            return -1;
        }
    }

    public static function DeleteNotValidPhrases($phrases=array()){
        global $all_bad_ph;
        foreach($phrases as $k=>$v){
            $v_arr = explode(' ', $v);
            if(strlen($v) >= 80 || count($v_arr) > 6){
                $all_bad_ph[] = $v;
                unset($phrases[$k]);
            }
        }
        return $phrases;
    }

    public static function GetPhrasesWithPlusAndSugar($phrases=array()){
        $phrases_with_plus = Phrases::GetPhrasesWithPlus($phrases);
        $phrases_with_sugar = Phrases::GetPhrasesWithSugar($phrases_with_plus, $phrases);

        return $phrases_with_sugar;
    }

    public static function GetPhrasesWithPlus($words=array()){
        $words_with_plus = array();

        for($i=0;$i<count($words);$i++){
            $tmp_word = '+'.str_replace(' ', ' +', trim($words[$i]));
            if(strlen($tmp_word) > 0 && strlen($tmp_word) <= 80){
                $words_with_plus[] = $tmp_word;
            }
        }

        return $words_with_plus;
    }

    public static function GetPhrasesWithSugar($words_with_plus=array(), $words=array()){
        $words_with_sugar = array();

        $sugar = Phrases::GetSugar();

        foreach($sugar as $k => $v){
            $tmp_words = array();

            for($i=0;$i<count($words);$i++){
                $tmp_word = trim($words[$i].' '.$v);
                $tmp_word_arr = explode(' ', $tmp_word);

                if(strlen($tmp_word) > 0 && strlen($tmp_word) < 80 && count($tmp_word_arr) <= 3){
                    $tmp_words[] = $tmp_word;
                }
            }

            if((10000 - count($words_with_sugar)) >= count($tmp_words)){
                $words_with_sugar = array_merge($words_with_sugar, $tmp_words);
            }else{
                break;
            }
        }

        foreach($sugar as $k => $v){
            $tmp_words = array();

            for($i=0;$i<count($words_with_plus);$i++){
                if($v != ''){
                    $tmp_word = $words_with_plus[$i].' +'.str_replace(' ', ' +', $v);
                }else{
                    $tmp_word = $words_with_plus[$i];
                }

                $tmp_word_arr = explode(' ', $tmp_word);
                if(strlen($tmp_word) > 0 && strlen($tmp_word) < 80 && count($tmp_word_arr) <= 6){
                    $tmp_words[] = $tmp_word;
                }
            }

            if((10000 - count($words_with_sugar)) >= count($tmp_words)){
                $words_with_sugar = array_merge($words_with_sugar, $tmp_words);
            }else{
                break;
            }
        }

        if(count($words_with_sugar) < 5000){
            foreach($sugar as $k => $v){
                $tmp_words = array();

                for($i=0;$i<count($words);$i++){
                    $tmp_word = trim($words[$i].' '.$v);
                    $tmp_word_arr = explode(' ', $tmp_word);

                    if(strlen($tmp_word) > 0 && strlen($tmp_word) < 80 && count($tmp_word_arr) > 3 && count($tmp_word_arr) <= 6){
                        $tmp_words[] = $tmp_word;
                    }
                }

                if((10000 - count($words_with_sugar)) >= count($tmp_words)){
                    $words_with_sugar = array_merge($words_with_sugar, $tmp_words);
                }else{
                    break;
                }
            }
        }

        return $words_with_sugar;
    }

    /**
     * @param array $words - массив слов
     * @param array $words_arr - массив строк и в каких кавычках/скобках  они заключенны
     * шаблон: ['word' => 'входящая фраза без скобок/кавычек', 'key' => 'скобка/кавычка']
     * @param array $brackets1 - массив с правыми скобками/кавычками
     * @param array $brackets2 - массив с левыми скобками/кавычками
     * @return array
     */
    public static function GetPhrase_no_google3($words=array(), $words_arr=array(), $brackets1=array(), $brackets2=array()){
        global $all_ph;
        $all_bad_ph = [];


        $delim=array('-','.','');

        $temp_words=array();
        $temp_words_low=array();

        //прохожу по всем словам и добавляю их в другой массив.
        //исключаю повторные слова.
        for($i=0;$i<count($words);$i++){
           //переводу их в нижний регистр
            $low=strtolower($words[$i]);
           //если есть слово и  его нет в массиве  temp_words, то добавляю его в этото массив
            if(/*!in_array($low, array('fillable', 'the', 'a', ' ', 'of')) && */!in_array($low,$temp_words_low) && strlen($low)>0){
                $temp_words[]=$words[$i];
                $temp_words_low[]=$low;
            }
        }

     /*  for($i=0;$i<count($temp_words);$i++){
            $all_ph[]=$temp_words[$i];
            Phrases::phrase($temp_words[$i],$temp_words,$i+1);
        }
        $all_ph_script1= $all_ph;
*/
       // $temp_words_low = ['How', 'How_to', 'fill'] ;
        $combinatorics = new \Math_Combinatorics;

        $all_ph_arrays = [];

        for($i=1; $i<=count($temp_words); $i++) {
            $all_ph_arrays = array_merge($all_ph_arrays,$combinatorics->combinations($temp_words, $i));
        }

        foreach ($all_ph_arrays as $phrase) {
            $string   = implode(' ', $phrase);
            if (strlen($string) <= 80) {
                $all_ph[] = $string;
            } else {
                $all_bad_ph[] = $string;
            }
        }


        foreach ($all_ph_arrays as $phrase) {
            $string   = implode(' ', $phrase);
            if (strlen($string) <= 80) {
                $all_ph[] = $string;
            } else {
                $all_bad_ph[] = $string;
            }
        }

        $check_sq = 0;
        foreach($words_arr as $wId=>$wVal){
            if($wVal['key'] == "["){
                foreach($words_arr as $wId2=>$wVal2){
                    if($wVal2['key'] == "{" && $wVal2['word'] == '['.$wVal['word'].']'){
                        $check_sq = 1;
                    }
                }
            }
        }

        foreach($words_arr as $wId=>$wVal){
            foreach($all_ph as $k=>$v){
                $origin_arr = explode(' ', $v);

                $wVal['word'] = str_replace($brackets1, ' ', $wVal['word']);
                $wVal['word'] = str_replace($brackets2, ' ', $wVal['word']);
                $wVal['word'] = trim(preg_replace('/[\s]+/', ' ', $wVal['word']));
                $wValArr = explode(' ', $wVal['word']);

                if($wVal['key'] == "'" || $wVal['key'] == '"'){ //если ' или "
                    if(!in_array($wVal['word'], $origin_arr)){
                        unset($all_ph[$k]);
                    }
                }elseif($wVal['key'] == "[" && $check_sq == 1){ //если {[]}
                    $words_count = 0;
                    $part = '';

                    $wVal['word'] = str_replace('_', ' ', $wVal['word']);
                    $wVal['word'] = trim(preg_replace('/[\s]+/', ' ', $wVal['word']));
                    $wValArr = explode(' ', $wVal['word']);

                    foreach($wValArr AS $awK=>$awV){
                        if(in_array($awV, $origin_arr)){
                            $words_count++;
                            $part .= ' '.$awV;
                        }
                    }

                    $part = trim($part);

                    if($words_count > 1){
                        for($j = 0; $j < count($delim); $j++){
                            $now_part = preg_replace("/ /", $delim[$j], $part);
                            $now = str_replace($part, $now_part, $v);

                            if(!in_array($now, $all_ph)){
                                $all_ph[] = $now;
                            }

                            foreach($delim as $id=>$val){
                                $part2 = $part;
                                $pos = 0;

                                while(strpos($part2, ' ') !== FALSE){
                                    $part_x = $part;

                                    $pos = strpos($part2, ' ', $pos);
                                    $part2 = substr_replace($part2, $val, $pos, 1);
                                    $part_x = substr_replace($part_x, $val, strpos($part, ' ', $pos), 1);

                                    $now = str_replace($part, $part2, $v);
                                    if(!in_array($now, $all_ph)){
                                        $all_ph[] = $now;
                                    }

                                    $now = str_replace($part, $part_x, $v);
                                    if(!in_array($now, $all_ph)){
                                        $all_ph[] = $now;
                                    }

                                    foreach($delim as $key=>$value){
                                        $part3 = str_replace(' ', $value, $part2);
                                        $now = str_replace($part, $part3, $v);

                                        if(!in_array($now, $all_ph)){
                                            $all_ph[] = $now;
                                        }

                                        $part3 = str_replace(' ', $value, $part_x);
                                        $now = str_replace($part, $part3, $v);

                                        if(!in_array($now, $all_ph)){
                                            $all_ph[] = $now;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }elseif($wVal['key'] == "("){ //если ()
                    $words_count = 0;

                    foreach($wValArr AS $wK=>$wV){
                        if(in_array($wV, $origin_arr) && strpos($v, $wV) !== FALSE){
                            $words_count++;
                        }
                    }

                    if(($words_count > 1 || strpos(' '.$v.' ', ' '.trim(str_replace('_', ' ',$wVal['word'])).' ') !== FALSE )){
                        unset($all_ph[$k]);
                    }
                }elseif($wVal['key'] == "{"){ //если {}
                    $words_count = 0;

                    foreach($wValArr AS $wK=>$wV){
                        if(in_array($wV, $origin_arr)){
                            $words_count++;
                        }
                    }

                    if($words_count != 0 && $words_count != count($wValArr) && strpos($v, $wVal['word']) === FALSE){
                        unset($all_ph[$k]);
                    }
                }
            }
        }

        foreach($words_arr as $wId=>$wVal){
            foreach($all_ph as $k=>$v){
                $v = str_replace('_', ' ', $v);
                $v = trim(preg_replace('/[\s]+/', ' ', $v));
                $all_ph[$k] = $v;

                $origin_arr = explode(' ', $v);

                $wVal['word'] = str_replace($brackets1, ' ', $wVal['word']);
                $wVal['word'] = str_replace($brackets2, ' ', $wVal['word']);
                $wVal['word'] = trim(preg_replace('/[\s]+/', ' ', $wVal['word']));
                $wValArr = explode(' ', $wVal['word']);

                if($wVal['key'] == "["){ //если []
                    $words_count = 0;
                    $part = '';

                    $wVal['word'] = str_replace('_', ' ', $wVal['word']);
                    $wVal['word'] = trim(preg_replace('/[\s]+/', ' ', $wVal['word']));
                    $wValArr = explode(' ', $wVal['word']);

                    foreach($wValArr AS $awK=>$awV){
                        if(in_array($awV, $origin_arr)){
                            $words_count++;
                            $part .= ' '.$awV;
                        }
                    }

                    $part = trim($part);

                    if($words_count > 1){
                        for($j = 0; $j < count($delim); $j++){
                            $now_part = preg_replace("/ /", $delim[$j], $part);
                            $now = str_replace($part, $now_part, $v);

                            if(!in_array($now, $all_ph)){
                                $all_ph[] = $now;
                            }

                            foreach($delim as $id=>$val){
                                $part2 = $part;
                                $pos = 0;

                                while(strpos($part2, ' ') !== FALSE){
                                    $part_x = $part;

                                    $pos = strpos($part2, ' ', $pos);
                                    $part2 = substr_replace($part2, $val, $pos, 1);
                                    $part_x = substr_replace($part_x, $val, strpos($part, ' ', $pos), 1);

                                    $now = str_replace($part, $part2, $v);
                                    if(!in_array($now, $all_ph)){
                                        $all_ph[] = $now;
                                    }

                                    $now = str_replace($part, $part_x, $v);
                                    if(!in_array($now, $all_ph)){
                                        $all_ph[] = $now;
                                    }

                                    foreach($delim as $key=>$value){
                                        $part3 = str_replace(' ', $value, $part2);
                                        $now = str_replace($part, $part3, $v);

                                        if(!in_array($now, $all_ph)){
                                            $all_ph[] = $now;
                                        }

                                        $part3 = str_replace(' ', $value, $part_x);
                                        $now = str_replace($part, $part3, $v);

                                        if(!in_array($now, $all_ph)){
                                            $all_ph[] = $now;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $all_ph;
    }
    /**
     * Функция, которая конкатенирует слова, через "_".
     * Заменяет пробелы на "_"
     * Слова для обработки должны находится между двумя скобками  $bracket_left  и $bracket_right
     * @param $phrase фраза, над которой будет идти операция
     * @param string $bracket_left - правая кавычка
     * @param string $bracket_right - левая кавычка
     * @return mixed -строка, в которой пробелы заменены на "_", между словами,
     * которые находяться между кавычками $bracket_left  и $bracket_right
     */
    public static function GetUpdatePhrase($phrase, $bracket_left='{', $bracket_right='}'){
        $keyword_tmp = $phrase;

        while(strpos($keyword_tmp, $bracket_left) !== FALSE){
            $pos_left = strpos($keyword_tmp, $bracket_left);
            $pos_right = @strpos($keyword_tmp, $bracket_right, $pos_left);

            if($pos_left !== FALSE && $pos_right !== FALSE){
                $tmp_keyword = substr($keyword_tmp, $pos_left+1, $pos_right-$pos_left-1);
                $tmp_keyword_new = str_replace(' ', '_', $tmp_keyword);
                $keyword_tmp = str_replace($bracket_left.$tmp_keyword.$bracket_right, $tmp_keyword_new, $keyword_tmp);
                $phrase = str_replace($tmp_keyword, $tmp_keyword_new, $phrase);
            }
        }

        return $phrase;
    }




    /**
     * Метод, получает строку, и возвращает массив массивов типа:
     * [
     *      'word' => 'входящая фраза без скобок/кавычек',
     *      'key' => 'скобка/кавычка'
     * ]
     * Пример:
     * print_r('{[some string]}', array('{','['), array('}',']'));
     *
     * Результат:
     * [
     *       [
     *          'word' => '[some string]',
     *          'key' => '{'
     *       ],
     *      [
     *          'word' => 'some string',
     *          'key' => '['
     *       ]
     * ]
     * Функция разбивает входящую строку, на подстроки, ограниченные кавычками/ скобками из массива $brackets1
     * Метод запускает рекурсивно. За один запуск метод  находит и создает один массив из подстроки.
     * @param $phrase string - строка, которую нужно обработать.
     * @param array $brackets1 array - массив с правыми кавычками
     * @param array $brackets2 array - массив с левыми кавычками
     * @return array - результирующий массив с элементами ['word' => 'входящая фраза без скобок/кавычек', 'key' => 'скобка/кавычка']
     */
    // разбираем фразу, делаем массив с пометкой фраз в скобках
    public static function GetWordsArray($phrase, $brackets1=array(), $brackets2=array()){
        $i = 0;
        $words_arr = array();

        //выполнять цикл до тех пор, пока в исходно строке будут все левые(открывающие) скобки и кавычки
        while(strpos($phrase, '[') !== FALSE || strpos($phrase, '{') !== FALSE
            || strpos($phrase, '(') !== FALSE || strpos($phrase, '"') !== FALSE
            || strpos($phrase, "'") !== FALSE){
            //в строке есть открывающие (левые) скобки или кавычки.
            $pos = -1;
            $br = '';
            $bKey = -1;
            $tmp_word = '';

            //цикл ищет самую первую левую(начинающеюся) скобку/кавычку

            //запускаю цикл по массиву из левых кавычек/скобку
            foreach($brackets1 AS $bId=>$bVal){
                //нахожу позицию кавычки в строке
                $pos_tmp = strpos($phrase, $bVal);
                //если кавычка есть
                if($pos_tmp !== FALSE){
                    //если позиция скобки меньше, чем предыддущая позиция скобки или  предыдущая позиция == -1
                    if($pos > $pos_tmp || $pos == -1){
                        /// то изменяю позицию предыдущей скобки, как настоящую
                        $pos = $pos_tmp;
                        //устанавливаю название скобки
                        $br = $bVal;
                        //устанавливаю ключ скобки
                        $bKey = $bId;
                    }
                }
            }
            //если  была найдена в строке левая скобка/кавычка
            if($pos > -1 && $br != '' && $bKey != -1){
                //то вырезаю это слово, которое находится в кавычках
                $tmp_word = substr($phrase, $pos+1, strpos($phrase, $brackets2[$bKey], $pos+1)-$pos-1);
            }

            $phrase = str_replace($brackets1[$bKey].$tmp_word.$brackets2[$bKey], ' ', $phrase);

            $words_arr[$i]['word'] = trim($tmp_word);
            $words_arr[$i]['key'] = $brackets1[$bKey];
            $words_arr[$i]['add_word'] = Phrases::GetWordsArray($words_arr[$i]['word'], $brackets1, $brackets2);
            $i++;
        }

        if(count($words_arr) > 0){
            $words_arr = Phrases::GetNewWordsArray($words_arr);
        }

        return $words_arr;
    }
    // переформировавыем массив для удобства обработки последовательно, начиная с внутренних скобок
    // только 3 уровня, при большем количестве скобок надо переделывать на рекурсию
    public static function GetNewWordsArray($words_arr = array()){
        $new_words_array = array();
        foreach($words_arr as $k=>$v){
            $v2=$v;
            unset($v2['add_word']);
            $new_words_array[] = $v2;

            if($v['add_word'] != ''){
                foreach($v['add_word'] as $key=>$value){
                    $new_words_array[] = $value;
                }
            }
        }

        return $new_words_array;
    }
    public static function GetSugar(){
        return  array(
            0 => '',
            1 => 'fillable',
            2 => 'fill online',
            3 => 'fill in',
            4 => 'fill out',
            5 => 'editable',
            6 => 'writeable',
            7 => 'printable',
            8 => 'sign',
            9 => 'pdf',
            10 => 'online',
            11 => 'blank',
            12 => 'fax',
            13 => 'email',
            14 => 'print',
            15 => 'download',
            16 => 'pdffiller'
        );

    }
}

