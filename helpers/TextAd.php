<?php
/**
 * User: Maxim Gavrilenko <maxim.gavrilenko@pdffiller.com>
 * Date: 15.12.15
 * Time: 14:27
 */

namespace denisog\gah\helpers;

use common\models\GoogleGroups;
use denisog\gah\helpers\Common;

class TextAd {

    const MATCH_TYPE_PHRASE = 'PHRASE';
    const MATCH_TYPE_BROAD  = 'BROAD';
    const MATCH_TYPE_EXACT  = 'EXACT';

    const MAX_LIMIT_FOR_QUERY = 2000;

    /**
     * Create (send) keyword to google adwords
     * @param array $textAds - textAds
     * * @param $adVersion - version
     * @param \AdWordsUser $user
     * @param \denisog\gah\models\AdWordsLocation $location
     * @return bool
     */
    static public function createAds(array $textAds, $adGroupId, $adVersion, \AdWordsUser $user)
    {
        if (empty($textAds) || count($textAds) > self::MAX_LIMIT_FOR_QUERY) {
            return false;
        }
        // Get the service, which loads the required classes.
        $adGroupAdService = $user->GetService('AdGroupAdService', $adVersion);
        $operations = [];
        foreach ($textAds as $textAd) {
            $operations[] = self::createAdProcess($textAd, $adGroupId);
        }
        // Make the mutate request.
        $result = $adGroupAdService->mutate($operations);
        // Display results.
        $ids = [];
        foreach ($result->value as $adGroupAd) {
            printf("Text ad with headline '%s' and ID '%s' was added.\n",
            $adGroupAd->ad->headline, $adGroupAd->ad->id);
            
            $ids[] = $adGroupAd->ad->id;
        }
        
        return $ids;
    }
    
    public static function createAdProcess(array $textAd, $adGroupId)
    {
        $newItem = new \TextAd();
        $newItem->headline = $textAd['headline'];
        $newItem->description1 = $textAd['description1'];
        $newItem->description2 = $textAd['description2'];
        $newItem->displayUrl = $textAd['displayUrl'];
        $newItem->finalUrls = array($textAd['finalUrl']);
        // Create ad group ad.
        $adGroupAd = new \AdGroupAd();
        $adGroupAd->adGroupId = $adGroupId;
        $adGroupAd->ad = $newItem;
        // Set additional settings (optional).
        $adGroupAd->status = $textAd['status'];
        // Create operation.
        $operation = new \AdGroupAdOperation();
        $operation->operand = $adGroupAd;
        $operation->operator = 'ADD';
        
        return $operation;
    }
}