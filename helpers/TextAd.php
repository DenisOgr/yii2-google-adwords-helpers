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

    public static $FIELDS = ['id', 'headline', 'description1', 'description2', 'displayUrl', 'finalUrl', 'status'];

    /**
     * Create (send) textAds to google adwords
     * @param array $textAds - textAds
     * * @param $adVersion - version
     * @param \AdWordsUser $user
     * @param \denisog\gah\models\AdWordsLocation $location
     * @return bool
     */
    public static function createAds(array $textAds, $adGroupId, $adVersion, \AdWordsUser $user, $validate = true)
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
            $operations[] = self::createAdProcess($textAd, $adGroupId);
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

    public static function createAdProcess(array $textAd, $adGroupId)
    {
        $newItem = new \TextAd();
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
        $item = new \TextAd();
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
}
