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

    const MAX_LIMIT_FOR_QUERY = 2000;
    public $fields = ['id', 'headline', 'description1', 'description2', 'displayUrl', 'finalUrl', 'status'];

    /**
     * Create (send) textAds to google adwords
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
        $items = [];
        foreach ($result->value as $adGroupAd) {
            printf("Text ad with headline '%s' and ID '%s' was added.\n",
            $adGroupAd->ad->headline, $adGroupAd->ad->id);
            
            $items[] = $adGroupAd->ad;
        }
        
        return $items;
    }
    
    public static function createAdProcess(array $textAd, $adGroupId)
    {
        $newItem = new \TextAd();
        foreach ($this->fields as $field) {
            if (isset($textAd[$field])) {
                if ($field = 'finalUrl') {
                    $newItem->finalUrls = [$textAd[$field]];
                }
            }
        }
        // Create ad group ad.
        $adGroupAd = new \AdGroupAd();
        $adGroupAd->adGroupId = $adGroupId;
        $adGroupAd->ad = $newItem;
        // Set additional settings (optional).
        if (isset($textAd['status'])) {
            $adGroupAd->status = $textAd['status'];
        }
        // Create operation.
        $operation = new \AdGroupAdOperation();
        $operation->operand = $adGroupAd;
        $operation->operator = 'ADD';
        
        return $operation;
    }
    
    /**
     * Update (send) textAds to google adwords
     * @param array $textAds - textAds
     * * @param $adVersion - version
     * @param \AdWordsUser $user
     * @param \denisog\gah\models\AdWordsLocation $location
     * @return bool
     */
    static public function updateAds(array $textAds, $adGroupId, $adVersion, \AdWordsUser $user)
    {
        if (empty($textAds) || count($textAds) > self::MAX_LIMIT_FOR_QUERY) {
            return false;
        }
        // Get the service, which loads the required classes.
        $adGroupAdService = $user->GetService('AdGroupAdService', $adVersion);
        $operations = [];
        foreach ($textAds as $textAd) {
            $operations[] = self::updateAdProcess($textAd, $adGroupId);
        }
        // Make the mutate request.
        $result = $adGroupAdService->mutate($operations);
        // Display results.
        $items = [];
        foreach ($result->value as $adGroupAd) {
            printf("Text ad with headline '%s' and ID '%s' was added.\n",
            $adGroupAd->ad->headline, $adGroupAd->ad->id);
            
            $items[] = $adGroupAd->ad;
        }
        
        return $items;
    }
    
    public static function updateAdProcess(array $textAd, $adGroupId = false)
    {
        $item = new \TextAd();
        foreach ($this->fields as $field) {
            if (isset($textAd[$field])) {
                if ($field == 'finalUrl') {
                    $item->finalUrls = [$textAd[$field]];
                } else {
                    $item->$field = [$textAd[$field]];
                }
            }
        }
        // Create ad group ad.
        $adGroupAd = new \AdGroupAd();
        if ($adGroupId) {
            $adGroupAd->adGroupId = $adGroupId;
        }
        $adGroupAd->ad = $newItem;
        // Set additional settings (optional).
        if (isset($textAd['status'])) {
            $adGroupAd->status = $textAd['status'];
        }
        // Create operation.
        $operation = new \AdGroupAdOperation();
        $operation->operand = $adGroupAd;
        $operation->operator = 'SET';
        
        return $operation;
    }
}