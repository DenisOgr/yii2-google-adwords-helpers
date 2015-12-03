<?php

/**
 * User: Andrew Temnokhud <andrewtemnokhud@gmail.com>
 * Date: 23.03.2015
 * Time: 14:27
 */

namespace denisog\gah\helpers;

use common\models\GoogleGroups;
use denisog\gah\helpers\Common;

class Label {

    const STATUS_ENABLED = 'ENABLED';

    public static function gelLabels($adVersion, \AdWordsUser $user) {
        $labels = [];

        // Get the service, which loads the required classes.
        $labelService = $user->GetService('LabelService', $adVersion);
        // Create selector.
        $selector = new \Selector();
        $selector->fields = array('LabelId', 'LabelName');
        $selector->ordering[] = new \OrderBy('LabelName', 'ASCENDING');
        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        do {
            // Make the get request.
            $page = $labelService->get($selector);
            if (isset($page->entries)) {
                foreach ($page->entries as $label) {
                    $labels[] = $label;
                }
            }
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $labels;
    }

    public static function setLabel($adVersion, \AdWordsUser $user, $criterionsId, $groupId, $labelId) {

        $adGroupCriterionService = $user->GetService('AdGroupCriterionService', $adVersion, NULL, NULL, NULL, TRUE);

        $operations = [];
        foreach ($criterionsId as $criterionId) {
            $adGroupCriterionLabel = new \AdGroupCriterionLabel();
            $adGroupCriterionLabel->adGroupId = $groupId;
            $adGroupCriterionLabel->criterionId = $criterionId;
            $adGroupCriterionLabel->labelId = $labelId;

            // Create operation.
            $operation = new \AdGroupCriterionLabelOperation();
            $operation->operand = $adGroupCriterionLabel;
            $operation->operator = 'ADD';

            $operations[] = $operation;
        }
//         Make the mutate request.
        $result = $adGroupCriterionService->mutateLabel($operations);
    }

    /**
     * Add labels
     * @param $adVersion
     * @param \AdWordsUser $user
     * @param array $labelOptions
     * @return mixed
     */
    public static function addLabel($adVersion,  \AdWordsUser $user, array $labelOptions)
    {
        $labelService = $user->GetService('LabelService', $adVersion);

        //357946720
        //test
        $label = new \TextLabel();
        $label->name      = $labelOptions['name'];
        $label->status    = !isset($labelOptions['status']) ? $labelOptions['status'] : self::STATUS_ENABLED;

        if (isset($labelOptions['attribute'])) {
            //https://developers.google.com/adwords/api/docs/reference/v201506/AdGroupService.DisplayAttribute
            $attribute = new \DisplayAttribute();
            if (isset($labelOptions['attribute']['description'])) {
                $attribute->description = (string)$labelOptions['attribute']['description'];
            }
            if (isset($labelOptions['attribute']['backgroundColor'])) {
                $attribute->backgroundColor = (string)$labelOptions['attribute']['backgroundColor'];
            }
            $label->attribute = $attribute;
        }

        $operation = new \LabelOperation();
        $operation->operand = $label;
        $operation->operator = 'ADD';
        $operations[] = $operation;

        $operations = array($operation);
        // Make the mutate request.
        $result = $labelService->mutate($operations);
        return $result->value[0];
    }

    /**
     * Update labels
     * @param $adVersion
     * @param \AdWordsUser $user
     * @param array $labelOptions
     * @return mixed
     */
    public static function updateLabel($labelId, $adVersion,  \AdWordsUser $user, array $labelOptions)
    {
        $labelService = $user->GetService('LabelService', $adVersion);

        //357946720
        //test
        $label = new \TextLabel();
        $label->id = $labelId;

        if (isset($labelOptions['name'])) {
            $label->name = (string)$labelOptions['name'];
        }
        if (isset($labelOptions['status'])) {
            $label->status = $labelOptions['status'];
        }

        if (isset($labelOptions['attribute'])) {
            //https://developers.google.com/adwords/api/docs/reference/v201506/AdGroupService.DisplayAttribute
            $attribute = new \DisplayAttribute();
            if (isset($labelOptions['attribute']['description'])) {
                $attribute->description = (string)$labelOptions['attribute']['description'];
            }
            if (isset($labelOptions['attribute']['backgroundColor'])) {
                $attribute->backgroundColor = (string)$labelOptions['attribute']['backgroundColor'];
            }
            $label->attribute = $attribute;
        }

        $operation = new \LabelOperation();
        $operation->operand = $label;
        $operation->operator = 'ADD';
        $operations[] = $operation;

        $operations = array($operation);
        // Make the mutate request.
        $result = $labelService->mutate($operations);
        return $result->value[0];
    }

}
