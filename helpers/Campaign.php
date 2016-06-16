<?php
/**
 * User: Maxim Gavrilenko
 * Date: 2/8/16
 * Time: 15:39
 */
namespace denisog\gah\helpers;

class Campaign
{
    public static function getCampaigns($adVersion, \AdWordsUser $user, $campaignIds, $fields = [])
    {
        // Get the service, which loads the required classes.
        $campaignService = $user->GetService('CampaignService', $adVersion);
        // Create selector.
        $selector = new \Selector();
        $selector->fields = $fields;
        $selector->ordering[] = new \OrderBy('Id', 'ASCENDING');
        // Create paging controls.
        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        // Create predicates.
        
        if ($campaignIds) {
            $selector->predicates[] = new \Predicate('Id', 'IN', $campaignIds);
        }
        $campaigns = [];
        do {
            // Make the get request.
            $page = $campaignService->get($selector);
            // Display results.
            if (isset($page->entries)) {
                foreach ($page->entries as $campaign) {
                    printf(
                        "Campaign with name '%s' and ID '%s' was found.\n",
                        $campaign->name,
                        $campaign->id
                    );
                    $campaigns[] = $campaign;
                }
            } else {
                print "No campaigns were found.\n";
            }
            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);
        return $campaigns;
    }
    
    public static function update($adVersion, \AdWordsUser $user, $campaignId, $data)
    {
        $operations = [];
        // Get the service, which loads the required classes.
        $campaignService = $user->GetService('CampaignService', $adVersion);
        // Create campaign using an existing ID.
        $campaign = new \Campaign();
        $campaign->id = $campaignId;
        $campaign->name = 'test campaign for programmers';
//        $campaign->status = 'PAUSED';
        $biddingStrategyConfiguration = new \BiddingStrategyConfiguration();
        $biddingStrategyConfiguration->biddingStrategyType = 'MANUAL_CPC';
        // You can optionally provide a bidding scheme in place of the type.
        $biddingScheme = new \ManualCpcBiddingScheme();
        $biddingScheme->enhancedCpcEnabled = false;
        $biddingStrategyConfiguration->biddingScheme = $biddingScheme;
        $campaign->biddingStrategyConfiguration = $biddingStrategyConfiguration;
        // Set additional settings (optional).
        
        $budget = new \Budget();
        $budget->name = 'Budget #' . uniqid();
        $budget->period = 'DAILY';
        $budget->amount = new \Money(100 * 1000000);
        $budget->deliveryMethod = 'STANDARD';
        // Create operation.
        $operation = new \BudgetOperation();
        $operation->operand = $budget;
        $operation->operator = 'ADD';
        $operations[] = $operation;
        
        // Create operation.
        $operation = new \CampaignOperation();
        $operation->operand = $campaign;
        $operation->operator = 'SET';
        $operations[] = $operation;
        // Make the mutate request.
        $result = $campaignService->mutate($operations);
        // Display result.
        $campaign = $result->value[0];
        printf("Campaign with ID '%s' was paused.\n", $campaign->id);
        return $campaign;
    }
    
    /**
     * Set modifier for bid.
     * @param AdWordsUser $user the user to run the example with
     * @param string $campaignId the id of the campaign to modify
     * @param float $bidModifier the multiplier to set on the campaign
     */
    public static function setBidModifier($adVersion, \AdWordsUser $user, $campaignId, $bidModifier = 0)
    {
        // Get the CampaignCriterionService, also loads classes
        $campaignCriterionService = $user->GetService('CampaignCriterionService', $adVersion);
        // Create Mobile Platform. The ID can be found in the documentation.
        // https://developers.google.com/adwords/api/docs/appendix/platforms
        $mobile = new \Platform();
        $mobile->id = 30001; // HighEndMobile = 30001
        // Create criterion with modified bid.
        $criterion = new \CampaignCriterion();
        $criterion->campaignId = $campaignId;
        $criterion->criterion = $mobile;
        $criterion->bidModifier = $bidModifier;
        // Create SET operation.
        $operation = new \CampaignCriterionOperation();
        $operation->operator = 'SET';
        $operation->operand = $criterion;
        // Update campaign criteria.
        $results = $campaignCriterionService->mutate(array($operation));
        // Display campaign criteria.
        if (count($results->value)) {
            foreach ($results->value as $campaignCriterion) {
                printf(
                    "Campaign criterion with campaign ID '%s', criterion ID '%s', "
                    . "and type '%s' was modified with bid %.2f.\n",
                    $campaignCriterion->campaignId,
                    $campaignCriterion->criterion->id,
                    $campaignCriterion->criterion->type,
                    $campaignCriterion->bidModifier
                );
            }
            return true;
        }
        print 'No campaign criterias were modified.';
    }
}
