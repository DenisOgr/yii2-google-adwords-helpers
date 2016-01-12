<?php
/**
 * User: Denis Porplenko <denis.porplenko@gmail.com>
 * Date: 20.08.14
 * Time: 14:27
 */

namespace denisog\gah\helpers;

use common\models\GoogleGroups;
use denisog\gah\helpers\Common;

class Keyword {

    const MATCH_TYPE_PHRASE = 'PHRASE';
    const MATCH_TYPE_BROAD  = 'BROAD';
    const MATCH_TYPE_EXACT  = 'EXACT';

    const SERVICE = 'AdGroupCriterionService';

    const MAX_LIMIT_FOR_QUERY = 2000;
    
    public static $FIELDS = ['id', 'status'];

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

        foreach (array_chunk($keywords, Keyword::MAX_LIMIT_FOR_QUERY) as $keywordItems) {

            $operations =[];

            foreach ($keywordItems as $keywordItem) {

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
            sleep(1);
        }
        return true;

    }


    /**
     * Get keywords by groups IDs
     * @param AdWordsUser $user the user to run the example with
     * @param string $adGroupId the id of the parent ad group
     */
    public static function getByGroups( $adVersion, \AdWordsUser $user,   array $adGroupId, array $settings = []) {
        //default settings
        $fields = !empty($settings['fields']) ? $settings['fields'] : ['KeywordText', 'KeywordMatchType', 'Id'];
        $orders = !empty($settings['order']) ? $settings['order'] : ['KeywordText', 'ASCENDING'];

        // Get the service, which loads the required classes.
        $adGroupCriterionService =
            $user->GetService('AdGroupCriterionService', $adVersion);

        // Create selector.
        $selector = new \Selector();
        $selector->fields = $fields;

        // Create predicates.
        $selector->predicates[] = new \Predicate('AdGroupId', 'IN', $adGroupId);
        if (isset($settings['keywordsIds'])) {
            $selector->predicates[] = new \Predicate('Id', 'IN', $settings['keywordsIds']);
        }
        if (isset($settings['keywordText'])) {
            $selector->predicates[] = new \Predicate('KeywordText', 'IN', $settings['keywordText']);
        }
        if (isset($settings['matchType'])) {
            $selector->predicates[] = new \Predicate('KeywordMatchType', 'IN', $settings['matchType']);
        }
        
        $selector->predicates[] =
            new \Predicate('CriteriaType', 'IN', array('KEYWORD'));

        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        $result = [];
        do {
            // Make the get request.
            $page = $adGroupCriterionService->get($selector);

            // Display results.
            if (isset($page->entries)) {
                foreach ($page->entries as $adGroupCriterion) {
                    $info             = get_object_vars($adGroupCriterion->criterion);
                    $info['group_id'] = $adGroupCriterion->adGroupId;
                    $info['status']   = $adGroupCriterion->userStatus;
                    $result[] = $info;

                    /*printf("Keyword with text '%s', match type '%s', and ID '%s' was "
                        . "found.\n", $adGroupCriterion->criterion->text,
                        $adGroupCriterion->criterion->matchType,
                        $adGroupCriterion->criterion->id);*/
                }
            } else {
                //print "No keywords were found.\n";
            }

            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);

        return $result;
    }

    /**
     * Set new bid in keyword
     * @author Nechaev Aleksand
     *
     * @param integer $keywordId
     * @param integer $groupId
     * @param number $bidAmmount
     * @param \AdWordsUser $user
     * @return null
     */
    static function setBid($adVersion, $keywordId, $groupId, $bidAmmount, \AdWordsUser $user){


            $adGroupCriterionService = $user->GetService('AdGroupCriterionService', $adVersion);

            $adGroupCriterion = new \BiddableAdGroupCriterion();
            $adGroupCriterion->adGroupId = $groupId;
            $adGroupCriterion->criterion = new \Criterion($keywordId);

            $bid = new \CpcBid();
            $maxCpc = $bidAmmount * 1000000;
            $bid->bid = new \Money($maxCpc);
            $biddingStrategyConfiguration = new \BiddingStrategyConfiguration();
            $biddingStrategyConfiguration->bids[] = $bid;
            $adGroupCriterion->biddingStrategyConfiguration = $biddingStrategyConfiguration;

            $operation = new \AdGroupCriterionOperation();
            $operation->operand = $adGroupCriterion;
            $operation->operator = 'SET';
            $operations = array($operation);

            $result = $adGroupCriterionService->mutate($operations);

            $adGroupCriterion = $result->value[0];

        return ($adGroupCriterion) ? $adGroupCriterion : false;
    }
    
    /**
     * Update (send) keyword to google adwords
     * @param array $keyword - example: ['id' => 1, 'status' => 'PAUSED']
     * @param integer $adGroupId
     * @param $adVersion - version
     * @param \AdWordsUser $user
     * @return bool
     */
    static function updateKeyword(array $keyword, $adGroupId, $adVersion, \AdWordsUser $user)
    {
        // Get the service, which loads the required classes.
        $adGroupCriterionService = $user->GetService('AdGroupCriterionService', $adVersion);
        // Create ad group criterion.
        $adGroupCriterion = new \BiddableAdGroupCriterion();
        $adGroupCriterion->adGroupId = $adGroupId;
        // Create criterion using an existing ID. Use the base class Criterion
        // instead of Keyword to avoid having to set keyword-specific fields.
        if (isset($keyword['id'])) {
            $adGroupCriterion->criterion = new \Criterion($keyword['id']);
        }
        if (isset($keyword['status'])) {
            $adGroupCriterion->userStatus = $keyword['status'];
        }
        // Update final URL.
        //  $adGroupCriterion->finalUrls = array('http://www.example.com/new');
        // Create operation.
        $operation = new \AdGroupCriterionOperation();
        $operation->operand = $adGroupCriterion;
        $operation->operator = 'SET';
        $operations = [$operation];
        // Make the mutate request.
        $result = $adGroupCriterionService->mutate($operations);
        // Display result.
        $adGroupCriterion = $result->value[0];

        return ($adGroupCriterion) ? $adGroupCriterion : false;
    }
}
