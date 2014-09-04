<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 9/2/14
 * Time: 2:22 PM
 */

namespace denisog\gah\helpers;


<<<<<<< HEAD
class PhrasesR {

    # List of tokens:
    # Opening brackets
   public static $op_br = ['zero_element', '(', '[', '{', "'", '<'];
    # Closing brackets
   public static $cl_br = ['zero_element', ')',  ']', '}', '"', '>'];
    # Internal symbols for words inside brackets
   public static $seps =[ ' ', '.', '-', '+'];


    public static function run($str)
    {
       # Simplify - brackets '[' not used without curly braces '{'
        $search    = ['{[', ']}', "`", "‘", "’"];
        $replace   = ['[', ']', "'", "'", '"'];

        $str       = str_replace($search, $replace, $str);

        $result = self::process_string($str);

        return array_map(function($data){return trim(preg_replace('/\s\s+/', ' ', $data));}, $result);
      }

    public static function process_string($str)
    {
        # Explode charachers
        $str_chars = str_split($str);
        # Brackets counter (number of opened: for closing bracket search)
        $br_count  = 0;
        # Flag if string really have brackets
        $has_br    = false; #false
        # Number of brackets at highest level
        $lev_br    = 0;
=======
use yii\base\Exception;

class PhrasesR {

    public static function run($str)
    {
        # Simplify - brackets '[' not used without curly braces '{'
        $search    = [']}', "`", "‘", "’"];
        $replace = [']', "'", "'", '"'];
        $str = str_replace($search, $replace, $str);

        # Explode charachers
        $str_chars = str_split($str);

        # Brackets counter (number of opened: for closing bracket search)
        $br_count = 0;
        # Flag if string really have brackets
        $has_br = false;#false
        # Number of brackets at highest level
        $lev_br = 0;
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948

        # List of currently opened brackets
        $bracket_list = []; #empty array

        # Cycle: check bracket validity
        foreach($str_chars as $work_pos => $char) {

            # BEGIN check bracket
            # Found any opening bracket
<<<<<<< HEAD
            if (in_array($str_chars[$work_pos], self::$op_br))
=======
            if (in_array($str_chars[$work_pos], $op_br))
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948
            {
                # Special case: apostrophe used as opening and closing bracket simultaneously
                if (($str_chars[$work_pos] == '\'') && ($br_count > 0))	# If there is any opened bracket
                {
<<<<<<< HEAD
                    if ($bracket_list[$br_count] == '\'')	# And currently searching for closing apostrophe
                    {
                        $str[$work_pos] = '"';		# Replace with quotemark for simplicity

                        $br_count = $br_count - 1;		# Closed bracket - less opened
                        $has_br   = true;				# Yes we have brackets

=======
                    if ($bracket_list[[$br_count]] == '\'')	# And currently searching for closing apostrophe
                    {
                        $str_chars[$work_pos] = '\"';		# Replace with quotemark for simplicity
                        $br_count = $br_count - 1;		# Closed bracket - less opened
                        $has_br = true;				# Yes we have brackets
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948
                        if ($br_count == 0)			# We closed brackets on highest level
                        {
                            $lev_br = $lev_br + 1;		# Mark complete brackets on highest level
                        }
<<<<<<< HEAD
                        continue;					# Go to next symbol
=======
                      continue;					# Go to next symbol
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948
                    }
                }

                # If we have any opening brace (including apostrophe which is not closing)
                $br_count = $br_count + 1;			# Mark for new bracket
<<<<<<< HEAD
                $bracket_list[$br_count] = $str_chars[$work_pos];	# Add bracket in list
                $has_br = true;
            }

            # Found any closing bracket (excluding apostrophe - see higher)
            if (in_array($str_chars[$work_pos], self::$cl_br))
=======
                $bracket_list[[$br_count]] = $str_chars[$work_pos];	# Add bracket in list
                $has_br = true;
            }
            # Found any closing bracket (excluding apostrophe - see higher)
            if (in_array($str_chars[$work_pos], $cl_br))
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948
            {
                if ($br_count == 0)		# If nothing opened
                {
                    throw new Exception('Closing bracket without opening');#Exception
                }
<<<<<<< HEAD
                if(array_search($bracket_list[$br_count], self::$op_br) != array_search($str_chars[$work_pos], self::$cl_br))  # If opened and closed bracket don't match
=======
                if(array_search($op_br,$bracket_list[[$br_count]]) != array_search($cl_br, $str_chars[$work_pos]))  # If opened and closed bracket don't match
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948
                {
                    throw new Exception('Non matching brackets');
                }

<<<<<<< HEAD
                $br_count = $br_count - 1; # Closed bracket - less opened
                $has_br   = true; # Yes we have brackets

                if ($br_count == 0)			# We closed brackets on highest level
                {
                    $lev_br = $lev_br + 1;			# Mark complete brackets on highest level
                }
=======
              $br_count = $br_count - 1; # Closed bracket - less opened
              $has_br   = true; # Yes we have brackets

              if ($br_count == 0)			# We closed brackets on highest level
              {
                  $lev_br = $lev_br + 1;			# Mark complete brackets on highest level
              }
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948
            }
        }	# END check bracket

        if ($br_count > 0)	# If any opening bracket left unclosed
        {
            throw new Exception('Missed closing brackets');
        }

        if (!$has_br)			# If no brackets at all - fixed string
        {
            $TMP = explode(' ', $str);

            if (count($TMP) > 1)
            {
<<<<<<< HEAD
                $combos = self::strToArrayWithEmpty($TMP);
                $TMP    = self::cartesian($combos);

                unset($TMP[count($TMP)-1]);
                foreach($TMP as $item) {
                    $result[] =  implode(' ', $item);
                }
                return $result;
            } else
            {
                return [$str]; #array of one string
            }
        } else {
            if (($lev_br == 1) && (in_array($str_chars[0], self::$op_br)) && (in_array($str_chars[count($str_chars) - 1], self::$cl_br)))	# If only one bracket on highest level for whole string
            {
                $start_pos = 1;		# Cut of brackets
                $work_pos  = 1;
                $max_pos   = count($str_chars) - 2;
                $operation = array_search($str_chars[0], self::$op_br);# Mark for special operation  вернуть индекс из массива op_br значение, которого равно str_chars[1]
            }  else				# We have not fixed string with more than one bracket and fixed string
            {
                $start_pos = 0;
                $work_pos  = 0;
                $max_pos   = count($str_chars) - 1;
                $operation = 0;
            }
        }
        if (($operation == 3) || ($operation == 5))	# 3 and 5 - fixed multiple words and single words
        {
            return [substr($str, 1, strlen($str)-2)];	# Cut of curly and angular braces
        } else {

            #//////////////////////////////////////////
            $positions = NULL;			# Positions of same level brackets (and fixed strings)
            $lengths   =   NULL;			# Lengths

            for($work_pos; $work_pos<=$max_pos; $work_pos ++)
            {
                # BEGIN Parsing cycle
                if (in_array($str_chars[$work_pos], self::$op_br))	# If opening bracket found
                {
                    $br_count ++;		# Count as one
                    if (($br_count == 1) && ($work_pos - $start_pos > 0))	# If it's first and not after previous bracket - it's after fixed string
                    {
                        $positions[] = $start_pos;			# Add fixed string
                        $lengths[]   = $work_pos - $start_pos;
                        $start_pos   = $work_pos;				# Mark begining position
                    }
                }
                else  if (in_array($str_chars[$work_pos], self::$cl_br))		# If closing bracket
                {
                    $br_count --;				# Count as closed
                    if ($br_count == 0)					# No opened left
                    {
                        $positions[] = $start_pos;
                        $lengths[]   = $work_pos - $start_pos + 1; # Add position of current braces
                        $start_pos   = $work_pos + 1; # Next braces should start at next symbol
                    }
                }
            }	# END Parsing cycle


            if ($start_pos <= $max_pos)		# If there is sting after last closing bracket
            {
                $positions[] = $start_pos;			# Add fixed string
                $lengths[]   = $max_pos - $start_pos + 1;
            }

            # Remove starting and trailing spaces, remove empty strings
            foreach($positions as $key => $position){
                $tmp = trim(substr($str, $positions[$key], $lengths[$key]));

                if(!empty($tmp)) {
                    $word_set[] = $tmp;
                }
            }
            #word_set = ['','','','','']
            #///////////////////////////////////////

            if (($operation == 0) && ($lev_br > 0))			# 0 - many brackets - make all combinations
            {
                //$word_set =['{[<DD><2842>]}', '<2842>']
                #рекурсивно запускаю process_string. функция возвращает массив строк.
                #построить все комбинации из массивов   $combinatorics->combinations($set, 3);
                # склеиваю все комбинации в строку. возвращаю массив строк.
                # return(apply(expand.grid(lapply(word_set, process_string)), 1, paste, collapse=' '));

                $a = [];

                foreach($word_set as $w) {
                    $a[] = self::process_string($w);
                }
                $a = self::cartesian($a);

                foreach($a as $item) {
                    $result[] =  implode(' ', $item);
                }
                return $result;
            } else if ($operation == 1)		# 1 - curve braces - list of words
            {
                # word_set =['{[<DD><2842>]}', '<2842>']
                #return(unique(c(unlist(lapply(word_set, process_string)), '')));
                #рекурсивно запускаю process_string. функция возвращает массив строк.
                #плюсы
                $a = [];
                foreach($word_set as $w) {
                    //array_push($a, self::process_string($w));
                    $a = array_merge($a, self::process_string($w));
                }
                $a[] = '';

                return $a;

            } else if ($operation == 2)		# 2 - brackets - combinatons with list of selected symbols
            {
                //word_set =['{[<DD><2842>]}', '<2842>']
                $a      = [];
                $result = [];

                foreach($word_set as $w) {
                    $a[] = [self::process_string($w), []];
                }
                $b = self::cartesian($a);
                unset($b[count($b) - 1]);
                foreach($b as $item) {
                    $item = self::clearArray($item);

                    if(count($item) == 1)
                    {

                        //array_push($result,  $item[0]);
                        $result = array_merge($result, $item[0]);
                    } else
                    {
                        for($i = count($item)-1; $i != 0; $i--) {
                            $current          = $item[$i];
                            $item[2 * $i]     = $current;
                            $item[$i * 2 - 1] = self::$seps;
                        }

                        $result_tmp = self::cartesian($item);

                        foreach($result_tmp as $item3){
                            $result[] =  implode('', $item3);
                        }
                    }
                }
                return $result;
            } else if ($operation == 4)	# 4 - apostrophes - various length combinations
            {
                //$word_set =['{[<DD><2842>]}', '<2842>']
                if (count($word_set) == 1) {
                    $result = self::process_string($word_set[0]);

                    #удаляю из массива пустые строки
                    return array_filter($result);
                } else {
                    $a      = [];
                    $result = [];

                    foreach($word_set as $w) {
                        $a[] = [self::process_string($w), []];
                    }
                    $b = self::cartesian($a);

                    unset($b[count($b) - 1]);

                    foreach($b as $item) {
                        $item = self::clearArray($item);

                        if(count($item) == 1) {
                            //array_push($result, $item[0]);
                            $result = array_merge($result, $item[0]);
                        } else {
                            $result_tmp = self::cartesian($item);

                            foreach($result_tmp as $item2){
                                $result[] =  implode(' ', $item2);
                            }
                        }
                    }
                    $result = array_filter(array_map('trim',array_unique($result)));
                    return $result;
                }
            }

=======
                $combos <- expand.grid(rep(list(c(F,T)), length(TMP)))[-1,];
              TMP <- unlist(apply(combos, 1, function(X){paste0(TMP[X], collapse=' ')}));
              return(TMP);#array of string
            }
                    else
                    {
                        return(str); #string
                    }
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948
        }

    }

<<<<<<< HEAD
    /**
     * @param array $input входящий массив массивов с развными вариантами.
     * @return array $output
     */
    public static function cartesian(array $input) {
        $result = array();

        while (list($key, $values) = each($input)) {
            // If a sub-array is empty, it doesn't affect the cartesian product
            if (empty($values)) {
                continue;
            }

            // Seeding the product array with the values from the first sub-array
            if (empty($result)) {
                foreach($values as $value) {
                    $result[] = array($key => $value);
                }
            }
            else {
                // Second and subsequent input sub-arrays work like this:
                //   1. In each existing array inside $product, add an item with
                //      key == $key and value == first item in input sub-array
                //   2. Then, for each remaining item in current input sub-array,
                //      add a copy of each existing array inside $product with
                //      key == $key and value == first item of input sub-array

                // Store all items to be added to $product here; adding them
                // inside the foreach will result in an infinite loop
                $append = array();

                foreach($result as &$product) {
                    // Do step 1 above. array_shift is not the most efficient, but
                    // it allows us to iterate over the rest of the items with a
                    // simple foreach, making the code short and easy to read.
                    $product[$key] = array_shift($values);

                    // $product is by reference (that's why the key we added above
                    // will appear in the end result), so make a copy of it here
                    $copy = $product;

                    // Do step 2 above.
                    foreach($values as $item) {
                        $copy[$key] = $item;
                        $append[] = $copy;
                    }

                    // Undo the side effecst of array_shift
                    array_unshift($values, $product[$key]);
                }

                // Out of the foreach, we can add to $results now
                $result = array_merge($result, $append);
            }
        }

        return $result;
    }

    /**
     * Конвертирует массив со строками в массив массивов строк + пустой элемент.
     * input: ['str1', 'str2', 'str3']
     * output:
     * [
     *  ['str1','']
     *  ['str2','']
     *  ['str3','']
     * ]
     * @param array $data
     * @return array
     */
    public static function strToArrayWithEmpty(array $data)
    {
        foreach ($data as $item) {
            $result[] = [$item, ''];
        }
        return $result;

    }

    /**Очищает массив от пустых массивов
     * @param array $item
     * @return array
     */
    public static function clearArray(array $item)
    {
        $result = [];
        foreach ($item as$key => $element) {
            if(!empty($element)) {
                $result[] = $item[$key];
            }
        }
        return $result;
    }
}

=======


    public static function test()
    {
        $s[] =  'form ({[<DD><2842>]}<2842>) (<PKI>{public key infrastructure}) certificate ({[<DA><5118>]}<5118>)';
        $s[] =  '{[<PTO><SB><131>]} 2006 IRS (<instruction><instructions>) form (<1023><i1023>)';
        $s[] =   "(<94><1994>) {[<200><FS><C3>]} annual vehicle '{inspection report} <form>' ({[<1099><SA>]}<1099>)";
        $s[] =   "(<94><1994>) {[<200><FS><C3>]} annual vehicle inspection report form ({[<1099><SA>]}<1099>)";
        $s[] =   "2012 FEMA form ({[<119><25><1>]}{[<119><25>]}) general admission (<app><application>)";
        $s[] =   "({How to}{How do I}{Where to}{Where do I}) (<search><get><find>) (<fillable><typeable><editable><printable>) 'Core Word' '(<application><documents><petition><request><form>)' (<pdf><doc><edit.doc>) (<fill><online><print><email><fax><sign><download><share><pdffiller>)";
        $s[] =  "‘{children travel}’ ‘(<application><documents><travel>)’";

        var_dump(self::run($s[0]));
    }
} 
>>>>>>> 9e209ba52b82c0c6193400a3c0044192fca12948
