<?php
/**
 * Created by Marketing Pdffiller.
 * User: Denis Porplenko <denis.porplenko@pdffiller.com>
 * Date: 12/3/15
 * Time: 4:51 PM
 */

namespace denisog\gah\helpers\labels_assign;

interface AddRemoveInterface
{
    public static function add(array $entities, $labelId, \AdWordsUser $user, $adVersion);
    public static function remove(array $entities, $labelId, \AdWordsUser $user, $adVersion);
    public static function update(array $entities, $labelId, \AdWordsUser $user, $adVersion);
}