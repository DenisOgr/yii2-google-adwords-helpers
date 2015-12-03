<?php
/**
 * Created by Marketing Pdffiller.
 * User: Denis Porplenko <denis.porplenko@pdffiller.com>
 * Date: 12/3/15
 * Time: 4:49 PM
 */

namespace denisog\gah\helpers\labels_assign;

class LabelToCampaign implements AddRemoveInterface
{

    public static function add(array $entities, $labelId, \AdWordsUser $user, $adVersion)
    {
        return self::action('ADD', $entities, $labelId, $user, $adVersion);
    }

    public static function remove(array $entities, $labelId, \AdWordsUser $user, $adVersion)
    {
        return self::action('REMOVE', $entities, $labelId, $user, $adVersion);
    }

    public static function update(array $entities, $labelId, \AdWordsUser $user, $adVersion)
    {
        return self::action('SET', $entities, $labelId, $user, $adVersion);
    }

    protected static function action($typeOperation, array $entities, $labelId, \AdWordsUser $user, $adVersion)
    {
        $service = $user->GetService('CampaignService', $adVersion, null, null, null, true);
        $operations = [];

        foreach ($entities as $entity) {
            $campaignLabel = new \CampaignLabel();
            $campaignLabel->campaignId = $entity->id;
            $campaignLabel->labelId = $labelId;

            $operation = new \CampaignLabelOperation();
            $operation->operand = $campaignLabel;
            $operation->operator = $typeOperation;

            $operations[] = $operation;
        }

        // Make the mutate request.
        $result = $service->mutateLabel($operations);
        return $result->value;
    }

} 