<?php

/**
 * Created by PhpStorm.
 * User: ubuntu-denis
 * Date: 8/22/14
 * Time: 5:22 PM
 */

namespace denisog\gah\helpers;

class Planner {

    function GetKeywordIdeas($adVersion, \AdWordsUser $user, $keywords) {
        $result = [];
        
        // Get the service, which loads the required classes.
        $targetingIdeaService = $user->GetService('TargetingIdeaService', $adVersion);

        // Create selector.
        $selector = new \TargetingIdeaSelector();

        $selector->requestType = 'STATS';
        $selector->ideaType = 'KEYWORD';
        $selector->requestedAttributeTypes = array('KEYWORD_TEXT', 'SEARCH_VOLUME',
            'CATEGORY_PRODUCTS_AND_SERVICES');
        
        $locationParameter = new \LocationSearchParameter ();
        $unitedStates = new \Location();
        $unitedStates->id = 2840;
        $locationParameter->locations = [$unitedStates];
        
        $networkParameter = new \NetworkSearchParameter ();
        $networdSettings = new \NetworkSetting();
        $networdSettings->targetGoogleSearch = true;
        $networdSettings->targetSearchNetwork = false;
        $networdSettings->targetContentNetwork = false;
        $networdSettings->targetPartnerSearchNetwork = false;
        $networkParameter->networkSetting = $networdSettings;
        
        // Create related to query search parameter.
        $relatedToQuerySearchParameter = new \RelatedToQuerySearchParameter();
        $relatedToQuerySearchParameter->queries = $keywords;
        $selector->searchParameters[] = $relatedToQuerySearchParameter;
        $selector->searchParameters[] = $locationParameter;
        $selector->searchParameters[] = $networkParameter;
        // Set selector paging (required by this service).
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        do {
            // Make the get request.
            $page = $targetingIdeaService->get($selector);
            // Display results.
            if (isset($page->entries)) {
                foreach ($page->entries as $targetingIdea) {
                    $data = \MapUtils::GetMap($targetingIdea->data);
                        $saerchValue = isset($data['SEARCH_VOLUME']->value) ? $data['SEARCH_VOLUME']->value : 0;
                        $result[] = ['keyword' => $data['KEYWORD_TEXT']->value, 'value' => $saerchValue];
                }
            } else {
                print "No keywords ideas were found.\n";
            }
            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);
        
        return $result;
    }

}
