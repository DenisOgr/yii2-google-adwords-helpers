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

}
