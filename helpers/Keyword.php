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

    static public function addKeyword($adVersion, \AdWordsUser $user, $text, \denisog\gah\models\AdWordsLocation $location, $matchType, $bidAmmount = false) {


        $service= $user->GetService(self::SERVICE, $adVersion);

        if($matchType === self::MATCH_TYPE_BROAD)
            $text = Common::convertSpaceToPlus($text);

        $keyword = new Keyword();
        $keyword->text = $text;
        $keyword->matchType = $matchType;

        $adGroupCriterion = new BiddableAdGroupCriterion();
        $adGroupCriterion->adGroupId = $location->group;
        $adGroupCriterion->criterion = $keyword;

        if($bidAmmount){

            $bid = new CpcBid();
            $maxCpc = $bidAmmount * ML;
            $bid->bid = new Money($maxCpc);
            $biddingStrategyConfiguration = new BiddingStrategyConfiguration();
            $biddingStrategyConfiguration->bids[] = $bid;
            $adGroupCriterion->biddingStrategyConfiguration = $biddingStrategyConfiguration;

        }

        $adGroupCriterion->userStatus = 'ENABLED';

        $operation = new AdGroupCriterionOperation();
        $operation->operand = $adGroupCriterion;
        $operation->operator = 'ADD';
        $operations[] = $operation;

        $results = $service->mutate($operations);

        return Common::getFirstRowFromResults($results);

    }
} 