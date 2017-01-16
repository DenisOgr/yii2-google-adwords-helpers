<?php
/**
 * User: Maxim Gavrilenko <maxim.gavrilenko@pdffiller.com>
 * Date: 15.12.15
 * Time: 14:27
 */
namespace denisog\gah\helpers;

use denisog\gah\helpers\Common;
use ErrorUtils;

class TextAd
{

    const MAX_LIMIT_FOR_QUERY = 2000;
    const STATUS_PAUSED = 'PAUSED';
    const STATUS_ENABLED = 'ENABLED';
    const STATUS_DISABLED = 'DISABLED';
    
    const STATUS_REMOVE = 'REMOVE';
    const STATUS_REMOVED = 'REMOVED';
    
    const AD_TYPE_DEF = 'default';
    const AD_TYPE_DSA = 'dsa';

    public static $FIELDS = ['id', 'headline', 'description1', 'description2', 'displayUrl', 'finalUrl', 'status'];

    /**
     * Create (send) textAds to google adwords
     * @param array $textAds - textAds
     * * @param $adVersion - version
     * @param \AdWordsUser $user
     * @param \denisog\gah\models\AdWordsLocation $location
     * @return bool
     */
    public static function createAds(array $textAds, $adGroupId, $adVersion, \AdWordsUser $user, $validate = true, $params = [])
    {
        require_once \Yii::getAlias(
            '@vendor/googleads/googleads-php-lib/src/Google/Api/Ads/Common/Util/ErrorUtils.php'
        );

        if (empty($textAds) || count($textAds) > self::MAX_LIMIT_FOR_QUERY) {
            return false;
        }
        // Get the service, which loads the required classes.
        $adGroupAdService = $user->GetService(
            'AdGroupAdService',
            $adVersion,
            null,
            null,
            ($validate) ? true : null
        );
        $operations = [];
        foreach ($textAds as $textAd) {
            $operations[] = self::createAdProcess($textAd, $adGroupId, $params);
        }

        try {
            // Make the mutate request.
            $result = $adGroupAdService->mutate($operations);
        } catch (\SoapFault $fault) {
            $errors = ErrorUtils::GetApiErrors($fault);
            if (sizeof($errors) == 0) {
                // Not an API error, so throw fault.
                throw $fault;
            }
            $operationIndicesToRemove = [];
            foreach ($errors as $error) {
                if ($error->ApiErrorType == 'PolicyViolationError') {
                    $operationIndex = ErrorUtils::GetSourceOperationIndex($error);
                    $operation = $operations[$operationIndex];
                    printf(
                        "Ad with headline '%s' violated %s policy '%s'.\n",
                        $operation->operand->ad->headline,
                        $error->isExemptable ? 'exemptable' : 'non-exemptable',
                        $error->externalPolicyName
                    );
                    if ($error->isExemptable) {
                        // Add exemption request to the operation.
                        printf("Adding exemption request for policy name '%s' on text "
                            . "'%s'.\n", $error->key->policyName, $error->key->violatingText);
                        $operation->exemptionRequests[] = new \ExemptionRequest($error->key);
                    } else {
                        var_dump($error);
                        // Remove non-exemptable operation.
                        print "Removing the operation from the request.\n";
                        $operationIndicesToRemove[] = $operationIndex;
                    }
                } else {
                    // Non-policy error returned, throw fault.
                    throw $fault;
                }
            }
            $operationIndicesToRemove = array_unique($operationIndicesToRemove);
            rsort($operationIndicesToRemove, SORT_NUMERIC);
            foreach ($operationIndicesToRemove as $operationIndex) {
                unset($operations[$operationIndex]);
            }
        }

        $items = [];
        if (sizeof($operations) > 0) {
            // Retry the mutate request.
            // Get the service, which loads the required classes.
            $adGroupAdServiceNoValidate = $user->GetService('AdGroupAdService', $adVersion);
            $result = $adGroupAdServiceNoValidate->mutate($operations);
            // Display results.
            foreach ($result->value as $adGroupAd) {
                printf(
                    "Text ad with headline '%s' and ID '%s' was added.\n",
                    $adGroupAd->ad->headline,
                    $adGroupAd->ad->id
                );
                $items[] = $adGroupAd->ad;
            }
        } else {
            print "All the operations were invalid with non-exemptable errors.\n";
            return [];
        }

        return $items;
    }

    public static function createAdProcess(
            array $textAd,
            $adGroupId,
            $params = [])
    {
        $textAdType = isset($params['textAdType']) ? $params['textAdType'] : self::AD_TYPE_DEF;

        switch ($textAdType) {
            case self::AD_TYPE_DSA:
                $newItem = new \DynamicSearchAd();
                break;
            
            case self::AD_TYPE_DEF:
                $newItem = new \TextAd();
                break;
        }
                
        foreach (self::$FIELDS as $field) {
            if (isset($textAd[$field])) {
                if ($field == 'finalUrl') {
                    $newItem->finalUrls = [$textAd[$field]];
                } elseif ($field != 'status') {
                    $newItem->$field = $textAd[$field];
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
     * @param array $textAds - textAds, element example: (['id' => 1, 'status' => 'PAUSED'])
     * @param $adVersion - version
     * @param \AdWordsUser $user
     * @return bool
     */
    public static function updateAds(array $textAds, $adGroupId, $adVersion, \AdWordsUser $user)
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
        return $adGroupAdService->mutate($operations);
    }

    public static function updateAdProcess(array $textAd, $adGroupId = false)
    {
        $item = new \Ad();
        foreach (self::$FIELDS as $field) {
            if (isset($textAd[$field])) {
                if ($field == 'status') {
                    continue;
                }

                if ($field == 'finalUrl') {
                    $item->finalUrls = [$textAd[$field]];
                } else {
                    $item->$field = $textAd[$field];
                }
            }
        }
        // Create ad group ad.
        $adGroupAd = new \AdGroupAd();
        if ($adGroupId) {
            $adGroupAd->adGroupId = $adGroupId;
        }
        $adGroupAd->ad = $item;
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
    
    /**
     * Remove textAds from google adwords
     * @param array $textAdsIds - textAd ids
     * @param $adVersion - version
     * @param \AdWordsUser $user
     * @return bool
     */
    public static function removeAds(array $textAdsIds, $adGroupId, $adVersion, \AdWordsUser $user)
    {
        if (empty($textAdsIds) || count($textAdsIds) > self::MAX_LIMIT_FOR_QUERY) {
            return false;
        }
        // Get the service, which loads the required classes.
        $adGroupAdService = $user->GetService('AdGroupAdService', $adVersion);
        $operations = [];
        foreach ($textAdsIds as $textAdId) {
            $operations[] = self::removeAdProcess($textAdId, $adGroupId);
        }
        // Make the mutate request.
        return $adGroupAdService->mutate($operations);
    }

    public static function removeAdProcess($textAdId, $adGroupId)
    {
        // Create base class ad to avoid setting type specific fields.
        $ad = new \Ad();
        $ad->id = $textAdId;
        // Create ad group ad.
        $adGroupAd = new \AdGroupAd();
        $adGroupAd->adGroupId = $adGroupId;
        $adGroupAd->ad = $ad;
        // Create operation.
        $operation = new \AdGroupAdOperation();
        $operation->operand = $adGroupAd;
        $operation->operator = 'REMOVE';
        
        return $operation;
    }
    
    /**
     * Remove textAds from google adwords
     * @param array $textAdsIds - textAd ids
     * @param $adVersion - version
     * @param \AdWordsUser $user
     * @return bool
     */
    public static function updateAdsStatus($textAdsIds, $adGroupId, $adVersion, \AdWordsUser $user, $status)
    {
        if (empty($textAdsIds) || count($textAdsIds) > self::MAX_LIMIT_FOR_QUERY) {
            return false;
        }
        // Get the service, which loads the required classes.
        $adGroupAdService = $user->GetService('AdGroupAdService', $adVersion);
        
        $operations = [];
        foreach ($textAdsIds as $textAdId) {
            $ad = new \Ad();
            $ad->id = $textAdId;
            // Create ad group ad.
            $adGroupAd = new \AdGroupAd();
            $adGroupAd->adGroupId = $adGroupId;
            $adGroupAd->ad = $ad;
            // Update the status.
            $adGroupAd->status = $status;
            // Create operation.
            $operation = new \AdGroupAdOperation();
            $operation->operand = $adGroupAd;
            $operation->operator = 'SET';
            
            $operations[] = $operation;
        }

        // Make the mutate request.
        return $adGroupAdService->mutate($operations);
    }
        
    public static function createDsaAutotarget($adVersion, \AdWordsUser $user, $adGroupId, $target, $params = array())
    {
        $adGroupCriterionService = $user->GetService('AdGroupCriterionService', $adVersion);
        
        $autotarget = new \WebpageCondition();
        $autotarget->operand =  'URL';
        $autotarget->argument =  $target;
        
        $param = new \WebpageParameter();
        $param->conditions = array($autotarget);
        $param->criterionName = $target;
        
        $webpage = new \Webpage();
        $webpage->parameter = $param;
        
        $bagc = new \BiddableAdGroupCriterion();
        $bagc->adGroupId = $adGroupId;
        $bagc->criterion = $webpage;
        $bagc->userStatus = self::STATUS_ENABLED;
        
        $bsc = new \BiddingStrategyConfiguration();
        
        $criterionOperation = new \AdGroupCriterionOperation();
        $criterionOperation->operator = 'ADD';
        $criterionOperation->operand = $bagc;
        
        $criterionAdOperation = array($criterionOperation);
        
        $result = $adGroupCriterionService->mutate($criterionAdOperation);
        return $result;
    }

    /**
     * removeDsaAutotarget
     * @param type $adVersion
     * @param \AdWordsUser $user
     * @param type $adGroupId
     * @param type $autotarget
     * @param type $params
     * @return type
     */
    function removeDsaAutotarget($adVersion, \AdWordsUser $user, $adGroupId, $autotarget, $params = array())
    {
        $adGroupCriterionService = $user->GetService('AdGroupCriterionService', $adVersion);
        
        $autotargetOld = new \WebpageCondition();
        $autotargetOld->operand =  'URL';
        $autotargetOld->argument =  $autotarget['url'];
        
        $paramOld = new \WebpageParameter();
        $paramOld->conditions = array($autotargetOld);
        $paramOld->criterionName = $autotarget['criterionName'];
        
        $webpageOld = new \Webpage();
        $webpageOld->parameter = $paramOld;
        $webpageOld->id = $autotarget['criterionId'];
        
        $bagcOld = new \BiddableAdGroupCriterion();
        $bagcOld->adGroupId = $adGroupId;
        $bagcOld->criterion = $webpageOld;
        $bagcOld->userStatus = self::STATUS_REMOVED;
        
        $criterionOperationRm = new \AdGroupCriterionOperation();
        $criterionOperationRm->operator = self::STATUS_REMOVE;
        $criterionOperationRm->operand = $bagcOld;
        
        $criterionAdOperation = array($criterionOperationRm);
        
        $result = $adGroupCriterionService->mutate($criterionAdOperation);
        return $result;
    }
    
    /**
     * getTextAds
     * 
     * @param type $adVersion
     * @param \AdWordsUser $user
     * @param int $adGroupId
     * @param array $params
     */
    function getTextAds($adVersion, \AdWordsUser $user, $adGroupId, $settings = array()) 
    {
        $ads = [];
        
        // Get the service, which loads the required classes.
        $adGroupAdService = $user->GetService('AdGroupAdService', $adVersion);
        
        $fields = !empty($settings['fields']) ? $settings['fields'] : ['Headline', 'Id'];
        $statuses = !empty($settings['statuses']) 
            ? $settings['statuses'] 
            : [self::STATUS_PAUSED, self::STATUS_ENABLED, self::STATUS_DISABLED];

        // Create selector.
        $selector = new \Selector();
        $selector->fields = $fields;
        $selector->ordering[] = new \OrderBy('Headline', 'ASCENDING');
        if (isset($settings['ids'])) {
            $selector->predicates[] = new \Predicate('Id', 'IN', $settings['ids']);
        }
        // Create predicates.
        $selector->predicates[] = new \Predicate('AdGroupId', 'IN', array($adGroupId));
        // By default disabled ads aren't returned by the selector. To return them
        // include the DISABLED status in a predicate.
        $selector->predicates[] = new \Predicate('Status', 'IN', $statuses);
        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        do {
            // Make the get request.
            $page = $adGroupAdService->get($selector);
            // Display results.
            if (isset($page->entries)) {
                foreach ($page->entries as $adGroupAd) {
                    $ads[] = $adGroupAd;
                }
            } else {
                print "No text ads were found.\n";
            }
            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);
        
        return $ads;
    }

}
