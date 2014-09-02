<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 9/2/14
 * Time: 2:22 PM
 */

namespace denisog\gah\helpers;


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

        # List of currently opened brackets
        $bracket_list = []; #empty array

        # Cycle: check bracket validity
        foreach($str_chars as $work_pos => $char) {

            # BEGIN check bracket
            # Found any opening bracket
            if (in_array($str_chars[$work_pos], $op_br))
            {
                # Special case: apostrophe used as opening and closing bracket simultaneously
                if (($str_chars[$work_pos] == '\'') && ($br_count > 0))	# If there is any opened bracket
                {
                    if ($bracket_list[[$br_count]] == '\'')	# And currently searching for closing apostrophe
                    {
                        $str_chars[$work_pos] = '\"';		# Replace with quotemark for simplicity
                        $br_count = $br_count - 1;		# Closed bracket - less opened
                        $has_br = true;				# Yes we have brackets
                        if ($br_count == 0)			# We closed brackets on highest level
                        {
                            $lev_br = $lev_br + 1;		# Mark complete brackets on highest level
                        }
                      continue;					# Go to next symbol
                    }
                }

                # If we have any opening brace (including apostrophe which is not closing)
                $br_count = $br_count + 1;			# Mark for new bracket
                $bracket_list[[$br_count]] = $str_chars[$work_pos];	# Add bracket in list
                $has_br = true;
            }
            # Found any closing bracket (excluding apostrophe - see higher)
            if (in_array($str_chars[$work_pos], $cl_br))
            {
                if ($br_count == 0)		# If nothing opened
                {
                    throw new Exception('Closing bracket without opening');#Exception
                }
                if(array_search($op_br,$bracket_list[[$br_count]]) != array_search($cl_br, $str_chars[$work_pos]))  # If opened and closed bracket don't match
                {
                    throw new Exception('Non matching brackets');
                }

              $br_count = $br_count - 1; # Closed bracket - less opened
              $has_br   = true; # Yes we have brackets

              if ($br_count == 0)			# We closed brackets on highest level
              {
                  $lev_br = $lev_br + 1;			# Mark complete brackets on highest level
              }
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
                $combos <- expand.grid(rep(list(c(F,T)), length(TMP)))[-1,];
              TMP <- unlist(apply(combos, 1, function(X){paste0(TMP[X], collapse=' ')}));
              return(TMP);#array of string
            }
                    else
                    {
                        return(str); #string
                    }
        }

    }



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