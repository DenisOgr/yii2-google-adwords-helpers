# > Test arguments

# str <- 'form ({[<DD><2842>]}<2842>) (<PKI>{public key infrastructure}) certificate ({[<DA><5118>]}<5118>)'
# str <- '[<PTO><SB><131>] 2006 IRS (<instruction><instructions>) form (<1023><i1023>)'
# str <- "(<94><1994>) {[<200><FS><C3>]} annual vehicle '{inspection report} <form>' ({[<1099><SA>]}<1099>)"
# str <- "(<94><1994>) {[<200><FS><C3>]} annual vehicle inspection report form ({[<1099><SA>]}<1099>)"
# str <- "2012 FEMA form ({[<119><25><1>]}{[<119><25>]}) general admission (<app><application>)"
# str <- "({How to}{How do I}{Where to}{Where do I}) (<search><get><find>) (<fillable><typeable><editable><printable>) 'Core Word' '(<application><documents><petition><request><form>)' (<pdf><doc><edit.doc>) (<fill><online><print><email><fax><sign><download><share><pdffiller>)"
# str <- "‘{children travel}’ ‘(<application><documents><travel>)’";
# filename <- "";

# > End test arguments

# > Command line arguments
args <- commandArgs(TRUE);
stopifnot(length(args) >= 2);
str <- args[1];
filename <- args[2];
# > End command line arguments

# Simplify - brackets '[' not used without curly braces '{'
str <- gsub('{[', '[', str, fixed=T);
str <- gsub(']}', ']', str, fixed=T);
# Simplify different types of apostrophes
str <- gsub("`", "'", str, fixed=T);
str <- gsub("‘", "'", str, fixed=T);
str <- gsub("’", '"', str, fixed=T);

# List of tokens:
# Opening brackets
op_br <- c('(', '[', '{', "'", '<')
# Closing brackets
cl_br <- c(')', ']', '}', '"', '>')
# Internal symbols for words inside brackets
seps <- c(' ', '.', '-', '+')

# Main function
process_string <- function (str)
{
  # Explode charachers
  str_chars <- strsplit(str, '')[[1]];
  
  # Brackets counter (number of opened: for closing bracket search)
  br_count <- 0;
  # Flag if string really have brackets
  has_br <- F;#false
  # Number of brackets at highest level
  lev_br <- 0;
  
  # List of currently opened brackets
  bracket_list <- list(); #empty array

  # Cycle: check bracket validity
  for (work_pos in 1:length(str_chars))
  {	# BEGIN check bracket
    # Found any opening bracket
    if (str_chars[work_pos] %in% op_br)
    {
      # Special case: apostrophe used as opening and closing bracket simultaneously
      if ((str_chars[work_pos] == '\'') && (br_count > 0))	# If there is any opened bracket
      {

        if (bracket_list[[br_count]] == '\'')	# And currently searching for closing apostrophe
        {
          str_chars[work_pos] = '\"';		# Replace with quotemark for simplicity
          br_count <- br_count - 1;		# Closed bracket - less opened
          has_br <- T;				# Yes we have brackets
          if (br_count == 0)			# We closed brackets on highest level
          {
            lev_br <- lev_br + 1;		# Mark complete brackets on highest level
          }
          next;					# Go to next symbol
        }
      }
      
      # If we have any opening brace (including apostrophe which is not closing)
      br_count <- br_count + 1;			# Mark for new bracket
      bracket_list[[br_count]] <- str_chars[work_pos];	# Add bracket in list
      has_br <- T;
    }
    
    # Found any closing bracket (excluding apostrophe - see higher)
    if (str_chars[work_pos] %in% cl_br)
    {
      if (br_count == 0)		# If nothing opened
      {
	stop('Closing bracket without opening');#Exception
      }
      
      if (which(op_br == bracket_list[[br_count]]) != which(cl_br == str_chars[work_pos]))	# If opened and closed bracket don't match
      {
	stop('Non matching brackets');
      }
      
      br_count <- br_count - 1;			# Closed bracket - less opened
      has_br <- T;				# Yes we have brackets
      
      if (br_count == 0)			# We closed brackets on highest level
      {
	lev_br <- lev_br + 1;			# Mark complete brackets on highest level
      }
    }
  }	# END check bracket

  if (br_count > 0)	# If any opening bracket left unclosed
  {
    stop('Missed closing brackets');
  }

  if (!has_br)			# If no brackets at all - fixed string
  {
    ############################################333
    #нужно разделить строку на массив строк, потом из каждой строки сделать массив с пустой строкой
    # например: было str =['1', '2'] стало: str = [[1, ''], [2, '']]
    #потом сделать cartesian. проимплодить результаты и удалить результат, где все пустые строки.
    TMP <- strsplit(str, ' ')[[1]];#TMP - array implode()
    if (length(TMP) > 1)
    {

      combos <- expand.grid(rep(list(c(F,T)), length(TMP)))[-1,];
      TMP <- unlist(apply(combos, 1, function(X){paste0(TMP[X], collapse=' ')}));
      return(TMP);#array of string
    }
    else
    {
      return(str); #string
    }
    ########################################
  }
  else 
  if ((lev_br == 1) && (str_chars[1] %in% op_br) && (str_chars[length(str_chars)] %in% cl_br))	# If only one bracket on highest level for whole string
  {
    start_pos <- 2;		# Cut of brackets
    work_pos <- 2;  
    max_pos <- length(str_chars) - 1
    operation <- which(op_br == str_chars[1]);	# Mark for special operation  вернуть индекс из массива op_br значение, которого равно str_chars[1]
  }
  else				# We have not fixed string with more than one bracket and fixed string
  {
    start_pos <- 1;
    work_pos <- 1;  
    max_pos <- length(str_chars)
    operation <- 0;
  }

  if ((operation == 3) || (operation == 5))	# 3 and 5 - fixed multiple words and single words
  {
    return (substr(str, 2, nchar(str)-1));	# Cut of curly and angular braces
  }
  else						# 0, 1, 2, 4 - different combinations
  {
    #//////////////////////////////////////////
    positions <- NULL;			# Positions of same level brackets (and fixed strings)
    lengths <- NULL;			# Lengths

    while (work_pos <= max_pos)		# Until the end
    {
    # BEGIN Parsing cycle
      if (str_chars[work_pos] %in% op_br)	# If opening bracket found
      {
	br_count <- br_count + 1;		# Count as one
	if ((br_count == 1) && (work_pos - start_pos > 0))	# If it's first and not after previous bracket - it's after fixed string
	{
	  positions <- c(positions, start_pos);			# Add fixed string
	  lengths <- c(lengths, work_pos - start_pos);
	  start_pos <- work_pos;				# Mark begining position
	}
      }
      else if (str_chars[work_pos] %in% cl_br)			# If closing bracket
      {
	br_count <- br_count - 1;				# Count as closed
	if (br_count == 0)					# No opened left
	{
	  positions <- c(positions, start_pos);			# Add position of current braces
	  lengths <- c(lengths, work_pos - start_pos + 1);
	  start_pos <- work_pos + 1;				# Next braces should start at next symbol
	}
      }
      work_pos <- work_pos + 1;					# Go to next symbol
    }	# END Parsing cycle
    
    if (start_pos <= max_pos)		# If there is sting after last closing bracket
    {
      positions <- c(positions, start_pos);			# Add fixed string
      lengths <- c(lengths, max_pos - start_pos + 1);
    }

	    # Remove starting and trailing spaces, remove empty strings
    word_set <- gsub('^( )*|( )*$', '', substring(str, positions, positions+lengths-1)) #trim
    word_set <- word_set[nchar(word_set) > 0]; #удаление пустых строк

    #word_set = ['','','','','']
    #///////////////////////////////////////
    if ((operation == 0) && (lev_br > 0))			# 0 - many brackets - make all combinations
    {
       word_set =['{[<DD><2842>]}', '<2842>']
            $a = [];

            foreach(word_set as w) {
            $a[] = (process_string(w))
            }
            $a = cartesian($a)

            foreach($a as $item){
            $result[] =  implode($item, ' ');
            }
            return $result;

      return(apply(expand.grid(lapply(word_set, process_string)), 1, paste, collapse=' '));
      #рекурсивно запускаю process_string. функция возвращает массив строк.
      #построить все комбинации из массивов   $combinatorics->combinations($set, 3);
      # склеиваю все комбинации в строку. возвращаю массив строк.

    }
    else if (operation == 1)		# 1 - curve braces - list of words
    {
     # word_set =['{[<DD><2842>]}', '<2842>']
     # $a = [];
      #foreach(word_set as w) {
       # array_push($a, process_string(w));
      #}
      #$a[] = '';
      #return $a;
      return(unique(c(unlist(lapply(word_set, process_string)), '')));
       #рекурсивно запускаю process_string. функция возвращает массив строк.
       #плюсы
    }
    else if (operation == 2)		# 2 - brackets - combinatons with list of selected symbols
    {
      word_set =['{[<DD><2842>]}', '<2842>']
      $a = [];
      $result =[];
      foreach(word_set as w) {
      $a[] = [(process_string(w)), []];
      }
      $b = cartesian($a)

      foreach($b as $item) {

          if(count(item) == 1) {
            array_push($result, item[0]);
          } else {
              for($i = count($item)-1; $i != 1; $i--) {
              $current = $item[$i];
              $item[2*$i] = current;
              $item[$i*2-1] = $seps
              }

              $result_tmp = cartesian($item);

              foreach($result_tmp as $item){
                $result[] =  implode($item, ' ');
              }

          }
      }
      return $result;


      TMP <- lapply(word_set, process_string);				# List of word lists for generating
      combos <- expand.grid(rep(list(c(F,T)), length(TMP)))[-1,];	# Boolean combinations for active lists
      TMP <- unlist(apply(combos, 1, 					# For all combination

	function(X){
	  active_set<-TMP[X];						# Get only active combos
	  if (length(active_set) > 1)					# If we have more than two
	  {	    
	    for (i in length(active_set):2)				# Add symbols between blocks
	    {
	      active_set[[i*2 - 1]] <- active_set[[i]];
	      active_set[[i*2 - 2]] <- seps;
	    }
	    return(unique(apply(expand.grid(active_set), 1, paste, collapse='')));	# Combine words
	  }
	  else
	  {
	    return(unlist(active_set));					# Only one block - words as is
	  }
	}));
    }
    else if (operation == 4)	# 4 - apostrophes - various length combinations
    {
      word_set =['{[<DD><2842>]}', '<2842>']
      if (count($word_set) == 1) {
        $result = process_string(word_set[0]);
       #удаляю из массива пустые строки
        foreach($result $key as $item) {
            if (empty($item)) {
                unset($result[$key]);
            }
        }
        return $result;
      } else {
        $a = [];
        $result =[];

        foreach(word_set as w) {
          $a[] = [(process_string(w)), []];
        }
      $b = cartesian($a);
      foreach($b as $item) {

          if(count(item) == 1) {
            array_push($result, item[0]);
          } else {
              $result_tmp = cartesian($item);

              foreach($result_tmp as $item){
                $result[] =  implode($item, ' ');
              }

          }
      }
     return  $result;
      }




      if (length(word_set) > 1)		# Multiple lists of words in apostrophes
      {
        TMP <- lapply(word_set, process_string);			# Recombine
        combos <- expand.grid(rep(list(c(F,T)), length(TMP)))[-1,];
        TMP <- unlist(apply(combos, 1, function(X){paste0(TMP[X], collapse=' ')}));
        return(unique(TMP));
      }
      else				# Single list - words without empty
      {
        TMP <- unlist(process_string(word_set));
        TMP <- TMP[nchar(TMP) > 0];
        return(TMP);
      }
    }
  }
}

# Save to file (with removing spaces at begin, end or double spaces)
write(gsub('(  )+', ' ', gsub('^( )*|( )*$', '', process_string(str))), file=filename)