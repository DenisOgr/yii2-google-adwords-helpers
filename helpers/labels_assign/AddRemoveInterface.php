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
    const ACTION_ADD    = 'ADD';
    const ACTION_REMOVE = 'REMOVE';
    const ACTION_SET    = 'SET';

    public static function add(array $entities, $labelId, \AdWordsUser $user, $adVersion);
    public static function remove(array $entities, $labelId, \AdWordsUser $user, $adVersion);
    public static function update(array $entities, $labelId, \AdWordsUser $user, $adVersion);
}