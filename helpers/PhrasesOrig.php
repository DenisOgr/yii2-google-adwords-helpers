<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 9/4/14
 * Time: 10:53 AM
 */

namespace denisog\gah\helpers;


class PhrasesOrig{
    public static function phrase($ph1, $words, $i) {
        global $all_ph, $all_bad_ph;

        for($j = $i; $j < count($words); $j++){
            $ph = $ph1.' '.$words[$j];

            if(!in_array($ph, $all_ph)){
                if(strlen($ph) >= 80){
                    $all_bad_ph[] = $ph;
                }else{
                    $all_ph[] = $ph;
                }

                if($j < count($words) - 1){
                    PhrasesOrig::phrase($ph, $words, $j+1);
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

        $brackets1 = array('[', '{', '(', '<', "'", '"');
        $brackets2 = array(']', '}', ')', '>', "'", '"');

        $manual_phrase = str_replace(array('<', '>'), ' ', $manual_phrase);
        $manual_phrase = trim(preg_replace('/[\s]+/', ' ', $manual_phrase));

        $manual_phrase = PhrasesOrig::GetUpdatePhrase($manual_phrase);
        $words_arr = PhrasesOrig::GetWordsArray($manual_phrase, $brackets1, $brackets2);

        $param = str_replace($brackets1, ' ', $manual_phrase);
        $param = str_replace($brackets2, ' ', $param);
        $param = trim(preg_replace('/[\s]+/', ' ', $param));

        $param_array = explode(' ', $param);

        $phrases = PhrasesOrig::GetPhrase_no_google3($param_array, $words_arr, $brackets1, $brackets2);
        $phrases = PhrasesOrig::DeleteNotValidPhrases($phrases);

        //sort($phrases);
        usort($phrases, array('denisog\gah\helpers\PhrasesOrig', 'cmp'));
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
        $phrases_with_plus = PhrasesOrig::GetPhrasesWithPlus($phrases);
        $phrases_with_sugar = PhrasesOrig::GetPhrasesWithSugar($phrases_with_plus, $phrases);

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

        $sugar = PhrasesOrig::GetSugar();

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
    public static function GetPhrase_no_google3($words=array(), $words_arr=array(), $brackets1=array(), $brackets2=array()){
        global $all_ph;

        $delim=array('-','.','');

        $temp_words=array();
        $temp_words_low=array();

        for($i=0;$i<count($words);$i++){
            $low=strtolower($words[$i]);
            if(/*!in_array($low, array('fillable', 'the', 'a', ' ', 'of')) && */!in_array($low,$temp_words_low) && strlen($low)>0){
                $temp_words[]=$words[$i];
                $temp_words_low[]=$low;
            }
        }

        for($i=0;$i<count($temp_words);$i++){
            $all_ph[]=$temp_words[$i];
            PhrasesOrig::phrase($temp_words[$i],$temp_words,$i+1);
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
    // фразу в фигурных скобках объединяем в одно слово вчерез '_'
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
    // разбираем фразу, делаем массив с пометкой фраз в скобках
    public static function GetWordsArray($phrase, $brackets1=array(), $brackets2=array()){
        $i = 0;
        $words_arr = array();

        while(strpos($phrase, '[') !== FALSE || strpos($phrase, '{') !== FALSE
            || strpos($phrase, '(') !== FALSE || strpos($phrase, '"') !== FALSE
            || strpos($phrase, "'") !== FALSE){
            $pos = -1;
            $br = '';
            $bKey = -1;
            $tmp_word = '';

            foreach($brackets1 AS $bId=>$bVal){
                $pos_tmp = strpos($phrase, $bVal);
                if($pos_tmp !== FALSE){
                    if($pos > $pos_tmp || $pos == -1){
                        $pos = $pos_tmp;
                        $br = $bVal;
                        $bKey = $bId;
                    }
                }
            }

            if($pos > -1 && $br != '' && $bKey != -1){
                $tmp_word = substr($phrase, $pos+1, strpos($phrase, $brackets2[$bKey], $pos+1)-$pos-1);
            }

            $phrase = str_replace($brackets1[$bKey].$tmp_word.$brackets2[$bKey], ' ', $phrase);

            $words_arr[$i]['word'] = trim($tmp_word);
            $words_arr[$i]['key'] = $brackets1[$bKey];
            $words_arr[$i]['add_word'] = PhrasesOrig::GetWordsArray($words_arr[$i]['word'], $brackets1, $brackets2);
            $i++;
        }

        if(count($words_arr) > 0){
            $words_arr = PhrasesOrig::GetNewWordsArray($words_arr);
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