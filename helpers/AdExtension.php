<?php
namespace denisog\gah\helpers;

class AdExtension
{
    public static function getCampaignsExtensions(\AdWordsUser $user, $params = [])
    {
        $defaultFields = ['CampaignId', 'Extensions', 'ExtensionType'];

        $version = \Yii::$app->params['google.services.settings']['version'];
        $service = $user->GetService('CampaignExtensionSettingService', $version);

        // Create selector.
        $selector = new \Selector();

        $selector->fields = isset($params['fields']) ? $params['fields'] : $defaultFields;

        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        $result = [];

        do {
            // Make the get request.
            $page = $service->get($selector);

            // Display results.
            if (isset($page->entries)) {
                foreach ($page->entries as $extensionSetting) {
                    if (!isset($result[$extensionSetting->campaignId])) {
                        $result[$extensionSetting->campaignId] = [];
                    }
                    $result[$extensionSetting->campaignId][$extensionSetting->extensionType] = $extensionSetting;
                }
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $result;
    }

    public static function getAccountExtensions(\AdWordsUser $user, $params = [])
    {
        $defaultFields = ['Extensions', 'ExtensionType'];

        $version = \Yii::$app->params['google.services.settings']['version'];
        $service = $user->GetService('CustomerExtensionSettingService', $version);

        // Create selector.
        $selector = new \Selector();
        $selector->fields = isset($params['fields']) ? $params['fields'] : $defaultFields;

        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        $result = [];

        do {
            // Make the get request.
            $page = $service->get($selector);

            // Display results.
            if (isset($page->entries)) {
                foreach ($page->entries as $extensionSetting) {
                    $result[$extensionSetting->extensionType] = $extensionSetting;
                }
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $result;
    }
}
