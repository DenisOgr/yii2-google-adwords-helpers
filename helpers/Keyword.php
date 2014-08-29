<?php
/**
 * User: Denis Porplenko <denis.porplenko@gmail.com>
 * Date: 20.08.14
 * Time: 14:27
 */

namespace denisog\gah\helpers;

use denisog\gah\helpers\Common;

class Keyword {

    const MATCH_TYPE_PHRASE = 'PHRASE';
    const MATCH_TYPE_BROAD  = 'BROAD';
    const MATCH_TYPE_EXACT  = 'EXACT';

    const SERVICE = 'AdGroupCriterionService';

    /**
     * Create (send) keyword to google adwords
     * @param array $keywords - keywords
     * @param $adVersion - version
     * @param \AdWordsUser $user
     * @param \denisog\gah\models\AdWordsLocation $location
     * @param array $settings
     * @return bool
     */
    static public function create(array $keywords, $adVersion, \AdWordsUser $user,  \denisog\gah\models\AdWordsLocation $location, array $settings) {

        $adGroupCriterionService =
            $user->GetService('AdGroupCriterionService', $adVersion);

        foreach ($keywords as $keywordItem) {

            // Create keyword criterion.
            $keyword            = new \Keyword();
            $keyword->text      = $keywordItem;
            $keyword->matchType = (isset($settings['matchType'])) ? $settings['matchType'] : self::MATCH_TYPE_BROAD;

            // Create biddable ad group criterion.
            $adGroupCriterion            = new \BiddableAdGroupCriterion();
            $adGroupCriterion->adGroupId = $location->group;
            $adGroupCriterion->criterion = $keyword;

            // Set additional settings (optional).
            if (isset($settings['userStatus'])) {
                $adGroupCriterion->userStatus = $settings['userStatus'];
            }

            if (isset($settings['destinationUrl'])) {
                $adGroupCriterion->destinationUrl = $settings['destinationUrl'];
            }
            if (isset($settings['setBid'])) {
                // Set bids (optional).
                $bid = new \CpcBid();
                $bid->bid =  new \Money($settings['setBid']);
                $biddingStrategyConfiguration = new \BiddingStrategyConfiguration();
                $biddingStrategyConfiguration->bids[] = $bid;
                $adGroupCriterion->biddingStrategyConfiguration = $biddingStrategyConfiguration;
                $adGroupCriteria[] = $adGroupCriterion;

            }
            // Create operation.
            $operation = new \AdGroupCriterionOperation();
            $operation->operand = $adGroupCriterion;
            $operation->operator = 'ADD';
            $operations[] = $operation;
        }

        // Make the mutate request.
        $result = $adGroupCriterionService->mutate($operations);

        return true;

    }
}