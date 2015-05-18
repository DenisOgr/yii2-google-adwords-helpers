<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 8/22/14
 * Time: 5:22 PM
 */

namespace denisog\gah\helpers;


use common\models\GoogleGroups;

class Group {
    public static function create($adVersion, \AdWordsUser $user, $campaignId, $groupName) {

        // Get the service, which loads the required classes.
        $adGroupService = $user->GetService('AdGroupService', $adVersion);


        // Create ad group.
        $adGroup = new \AdGroup();
        $adGroup->campaignId = $campaignId;
        $adGroup->name = $groupName;

        // Set bids (required).
        $bid = new \CpcBid();
        $bid->bid =  new \Money(1000000);
        $biddingStrategyConfiguration = new \BiddingStrategyConfiguration();
        $biddingStrategyConfiguration->bids[] = $bid;
        $adGroup->biddingStrategyConfiguration = $biddingStrategyConfiguration;

        // Set additional settings (optional).
        $adGroup->status = 'ENABLED';

        // Targetting restriction settings - these setting only affect serving
        // for the Display Network.
        $targetingSetting = new \TargetingSetting();
        // Restricting to serve ads that match your ad group placements.
        $targetingSetting->details[] =
            new \TargetingSettingDetail('PLACEMENT', TRUE);
        // Using your ad group verticals only for bidding.
        $targetingSetting->details[] =
            new \TargetingSettingDetail('VERTICAL', FALSE);
        $adGroup->settings[] = $targetingSetting;

        // Create operation.
        $operation = new \AdGroupOperation();
        $operation->operand = $adGroup;
        $operation->operator = 'ADD';
        $operations[] = $operation;
        // Make the mutate request.
        $result = $adGroupService->mutate($operations);

        // Display result.
        return $result->value;
    }

    public static function getAdGroups($adVersion, \AdWordsUser $user, $campaignId, $adGroupId = null) {
        $adGroups = null;

        // Get the service, which loads the required classes.
        $adGroupService = $user->GetService('AdGroupService', $adVersion);
        // Create selector.
        $selector = new \Selector();
        $selector->fields = array('Id', 'Name');
        $selector->ordering[] = new \OrderBy('Name', 'ASCENDING');
        // Create predicates.
        $selector->predicates[] = new \Predicate('CampaignId', 'IN', array($campaignId));
        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        do {
            // Make the get request.
            $page = $adGroupService->get($selector);
            // Display results.
            if (isset($page->entries)) {
                foreach ($page->entries as $adGroup) {
//                    $adGroups[] = $adGroup;
                    if (is_null($adGroupId)){
                        $adGroups[] = ['id' => $adGroup->id, 'name' => $adGroup->name];
                    } elseif ($adGroup->id == $adGroupId) {
                        $adGroups = ['id' => $adGroup->id, 'name' => $adGroup->name];
                        break;
                    }
                }
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $adGroups;
    }

    public static function addAdGroupUserList($adVersion, \AdWordsUser $user, $adGroupId, $listId) {
        // Get the AdGroupCriterionService, which loads the required classes.
        $adGroupCriterionService = $user->GetService('AdGroupCriterionService', $adVersion);
        // Create biddable ad group criterion for gender

        $userList = new \CriterionUserList();
        $userList->userListId = $listId;
        $userListAdGroupCriterion = new \BiddableAdGroupCriterion();
        $userListAdGroupCriterion->adGroupId = $adGroupId;
        $userListAdGroupCriterion->criterion = $userList;

        $adGroupCriterionOperation = new \AdGroupCriterionOperation();
        $adGroupCriterionOperation->operand = $userListAdGroupCriterion;
        $adGroupCriterionOperation->operator = 'ADD';
        $operations[] = $adGroupCriterionOperation;
        // Make the mutate request.
        $result = $adGroupCriterionService->mutate($operations);
        // Display results.
        foreach ($result->value as $adGroupCriterion) {
            printf("Ad group criterion with ad group ID '%s', criterion ID '%s' " .
                "and type '%s' was added.\n", $adGroupCriterion->adGroupId, $adGroupCriterion->criterion->id, $adGroupCriterion->criterion->CriterionType);
        }
    }

    public static function updateAdGroupTarget($adVersion, \AdWordsUser $user, $adGroupId, $target, $data) {
        // Get the service, which loads the required classes.
        $adGroupService = $user->GetService('AdGroupService', $adVersion);

        // Create ad group using an existing ID.
        $adGroup = new \AdGroup();
        $adGroup->id = $adGroupId;

        $targetingSetting = new \TargetingSetting();
        $targetingSetting->details[] =  new \TargetingSettingDetail($target, $data);
        $adGroup->settings[] = $targetingSetting;

        // Create operation.
        $operation = new \AdGroupOperation();
        $operation->operand = $adGroup;
        $operation->operator = 'SET';

        $operations = array($operation);

        // Make the mutate request.
        $result = $adGroupService->mutate($operations);

        // Display result.
        $adGroup = $result->value[0];
        echo("Ad group with ID " . $adGroup->id);
    }

    public static function getAdGroupUserList($adVersion, \AdWordsUser $user, $adGroupId) {
        $list = [];

        // Get the service, which loads the required classes.
        $adGroupService = $user->GetService('AdGroupCriterionService', $adVersion);
        // Create selector.
        $selector = new \Selector();
        $selector->fields = array('AdGroupId', 'UserListId', 'UserListName');
        $selector->ordering[] = new \OrderBy('UserListName', 'ASCENDING');
        // Create predicates.
        $selector->predicates[] = new \Predicate('AdGroupId', 'IN', array($adGroupId));
        $selector->predicates[] = new \Predicate('CriteriaType', 'IN', array('USER_LIST'));
        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        do {
            // Make the get request.
            $page = $adGroupService->get($selector);
            // Display results.
            if (isset($page->entries)) {
                foreach ($page->entries as $userList) {
                    $list[] = ['id' => $userList->criterion->userListId, 'name' => $userList->criterion->userListName];
                }
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $list;
    }

    /**
     * Update bid
     * @param $adVersion
     * @param GoogleGroups $group
     * @param $bidAmmount
     * @param \AdWordsUser $user
     * @return mixed
     */
    public static function updateBid($adVersion, GoogleGroups $group, $bidAmmount, \AdWordsUser $user)
    {
        // Get the service, which loads the required classes.
        $adGroupService = $user->GetService('AdGroupService', $adVersion);
        $bidAmmount = round($bidAmmount, 2);

        // Create ad group using an existing ID.
        $adGroup = new \AdGroup();
        $adGroup->id = $group->id;

        // Update the bid.
        $bid = new \CpcBid();
        $bid->bid =  new \Money($bidAmmount * \AdWordsConstants::MICROS_PER_DOLLAR);
        $biddingStrategyConfiguration = new \BiddingStrategyConfiguration();
        $biddingStrategyConfiguration->bids[] = $bid;
        $adGroup->biddingStrategyConfiguration = $biddingStrategyConfiguration;

        // Create operation.
        $operation = new \AdGroupOperation();
        $operation->operand = $adGroup;
        $operation->operator = 'SET';

        $operations = array($operation);

        // Make the mutate request.
        return $adGroupService->mutate($operations);

        // Display result.
//        $adGroup = $result->value[0];
//        printf("Ad group with ID '%s' has updated default bid '$%s'.\n", $adGroup->id,
//            $adGroup->biddingStrategyConfiguration->bids[0]->bid->microAmount /
//            \AdWordsConstants::MICROS_PER_DOLLAR);
    }
} 