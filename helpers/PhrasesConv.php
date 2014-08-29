<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 8/29/14
 * Time: 10:27 AM
 */

namespace denisog\gah\helpers;

/**
 * {a | b | c} = a || b || c
'a'            = a (must be in every keyword)
[a | b | c]    = a || b || c || ''

 *
 *
 * Class PhrasesConv
 * @package denisog\gah\helpers
 */
class PhrasesConv {
    public static function get($string)
    {
        //({a|b|c})([a|b|c])('core word')([a|b|c])
        /*
         * 1. Делю на массивы каждый сектор
         * 2. Формирую массивы в зависимости от того, что нужно:
         * ({a|b|c})     - [a,b,c]
         * ([a|b|c])     - [a,b,c,'']
         * ('core word') - ['core word']
         *
         * 3. Все массивы в нужном порядке складываю в один большой массив
         * 4. Запускаю функцию, которая формирует разные строки (массивы конвертирую в строки, при этом проверяю размер их не больше 80 символов)
         * 5. Если нужно, что бы все  отдельные слова тоже были ключевыми, то удаляю из строки все з спец символы, делю на пробелы,
         * и создаю массив только из слов. При этом удаляю все повторяющиеся элементы.
         *
         * */
    }

    /**
     * Функция генерирует все возможные варианты множеств из подмножеств.
     * Input:
     * $input = array(
        '1' => array('hi', 'hello'),
        '2' => array('den','sash'),
        '3' => array('nice'),
        '4' => array('goodbye', 'bye', 'foo')
        );
     *
     * Result:
     * Array
            (
            [0] => Array
            (
                [1] => hi
                [2] => den
                [3] => nice
                [4] => goodbye
            )

            [1] => Array
            (
                [1] => hello
                [2] => den
                [3] => nice
                [4] => goodbye
            )

            [2] => Array
            (
                [1] => hi
                [2] => sash
                [3] => nice
                [4] => goodbye
            )

            [3] => Array
            (
                [1] => hello
                [2] => sash
                [3] => nice
                [4] => goodbye
            )
      ....................
     )
     * @param array $input
     * @return array
     */
    public static function cartesian( array $input) {
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
} 