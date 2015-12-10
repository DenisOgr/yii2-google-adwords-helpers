<?php
/**
 * Created by Marketing Pdffiller.
 * User: Denis Porplenko <denis.porplenko@pdffiller.com>
 * Date: 12/3/15
 * Time: 4:49 PM
 */

namespace denisog\gah\helpers\labels_assign;

class LabelToKeyword implements AddRemoveInterface
{
    public static function add(array $entities, $labelId, \AdWordsUser $user, $adVersion)
    {
        return self::action(self::ACTION_ADD, $entities, $labelId, $user, $adVersion);
    }

    public static function remove(array $entities, $labelId, \AdWordsUser $user, $adVersion)
    {
        return self::action(self::ACTION_REMOVE, $entities, $labelId, $user, $adVersion);
    }

    public static function update(array $entities, $labelId, \AdWordsUser $user, $adVersion)
    {
        return self::action(self::ACTION_SET, $entities, $labelId, $user, $adVersion);
    }

    protected static function action($typeOperation, array $entities, $labelId, \AdWordsUser $user, $adVersion)
    {
        $service = $user->GetService('AdGroupCriterionService', $adVersion, null, null, null, true);
        $result = [];

        foreach (array_chunk($entities, self::COUNT_ENTITY_IN_CHUNK) as $chunkNumber => $entityInChunk) {
            $operations = [];
            foreach ($entityInChunk as $entity) {
                //set key
                $entity =!is_array($entity) ? $entity->attributes : $entity;
                $label = new \AdGroupCriterionLabel();
                $label->adGroupId   = $entity['group_id'];
                $label->criterionId = $entity['keyword_id'];
                $label->labelId = $labelId;

                $operation = new \AdGroupCriterionLabelOperation();
                $operation->operand = $label;
                $operation->operator = $typeOperation;

                $operations[] = $operation;
            }
            // Make the mutate request.
            $result[$chunkNumber] = $service->mutateLabel($operations);
        }

        return $result;
    }
}