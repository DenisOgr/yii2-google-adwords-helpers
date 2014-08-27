<?php
/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 8/22/14
 * Time: 5:22 PM
 */

namespace denisog\gah\helpers;


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
} 