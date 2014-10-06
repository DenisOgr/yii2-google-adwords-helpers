<?php
/**
 * Created by marketing.
 * @author: Denis Porplenko <denis.porplenko@pdffiller.com>
 * @date: 10/2/14
 */

namespace denisog\gah\helpers;
use denisog\gah\models\AdWordsLocation;
use yii\base\InvalidConfigException;


/**
 * Class help create url to adwords google.
 * @author: Denis Porplenko <denis.porplenko@pdffiller.com>
 * Use
 * AdwordsUrl::get(AdwordsUrl::LEVEL_ADGROUP, AdwordsUrl::SUBLEVEL_KEYWORDS,
new AdWordsLocation('429-518-9335','144070000', 1756339240, 4054957800), 7476380198)
*/

class AdwordsUrl {

    /**
     * Account level
     */
    const LEVEL_ACCOUNT = 'Account';

    /**
     * Campaign level
     */
    const LEVEL_CAMPAIGN = 'Campaign';

    /**
     * Adgroup level
     */
    const LEVEL_ADGROUP = 'AdGroups';

    /**
     * Sublevel campaign
     */
    const SUBLEVEL_CAMPAIGN = 'cm';

    /**
     * Sublevel Adgroup
     */
    const SUBLEVEL_ADGROUP = 'ag';

    /**
     * Sublevel Settings:All settings
     */

    const SUBLEVEL_SETTINGS = 'st_sum';

    /**
     *
     * Sublevel Settings:Locations
     */

    const SUBLEVEL_LOCATION_SETTINGS = 'st_loc';

    /**
     * Sublevel Settings Ad schedule
     */

    const SUBLEVEL_AD_SCHUDULE_SETTINGS = 'st_as';

    /**
     * Sublevel Settings Devices
     */
    const SUBLEVEL_DEVICES_SETTINGS = 'st_p';

    /**
     * Sublevel Ads
     */
    const SUBLEVEL_ADS= 'create';

    /**
     * Sublevel Keywords
     */
    const SUBLEVEL_KEYWORDS= 'key';

    /**
     * Sublevel Audiences
     */
    const SUBLEVEL_AUDIENCES = 'au';

    /**
     * Sublevel Ad extensions
     */
    const SUBLEVEL_AD_EXTENTIONS = 'ae';

    /**
     * Sublevel Dimensions
     */
    const SUBLEVEL_DIMENSIONS = 'di';

    /**
     * Sublevel Auto targets
     */
    const SUBLEVEL_AUTO_TARGETS = 'at';

    /**
     * User id in url. Please, set it from right url. In calid url it parametr:"__u" from the url
     * Sample: https://adwords.google.com/cm/CampaignMgmt?__u=1234567890
     * @var string
     * @kostyaId '7476380198'
     */
    public static $effectiveUserId;

    /**
     * Url to google adwords
     * @var string
     */
    public static $base = 'https://adwords.google.com/cm/CampaignMgmt?';


    public static function get($level, $subLevel, AdWordsLocation $option, $effectiveUserId=null)
    {
        if(!empty($effectiveUserId)) {
            self::$effectiveUserId = $effectiveUserId;
        }
        if (empty(self::$effectiveUserId)) {
            throw new InvalidConfigException('Empty effectiveUserId');
        }

        if (empty($option->clientNumber)) {
            throw new InvalidConfigException('Empty clientNumber');
        }

        $url = self::$base . "__c=" . $option->clientNumber . "&__u=" . self::$effectiveUserId . "#";

        switch($level) {

            case self::LEVEL_ACCOUNT: {
                //в конец добавить #r.ONLINE.SUBLEVEL
                $url .= "r.ONLINE";
                break;
            }

            case self::LEVEL_CAMPAIGN: {
                // в конец c.$option->campaign.SUBLEVEL
                if (empty($option->campaign)) {
                    throw new InvalidConfigException('Empty campaign in AdWordsLocation');
                }
                $url .= "c.{$option->campaign}";
                break;
            }

            case self::LEVEL_ADGROUP: {
                // в конец a.$option->group_$option->campaign.SUBLEVEL
                if (empty($option->campaign)) {
                    throw new InvalidConfigException('Empty campaign in AdWordsLocation');
                }
                if (empty($option->group)) {
                    throw new InvalidConfigException('Empty group in AdWordsLocation');
                }
                $url .= "a.{$option->group}_{$option->campaign}";
                break;
            }
        }
        return $url . ".{$subLevel}&app=cm";
    }

}

/*
 * https://adwords.google.com/cm/CampaignMgmt?__c=4054957800&authuser=0&__u=7476380198&syncServiceIdentity=true#r.ONLINE&app=cm
 * https://adwords.google.com/cm/CampaignMgmt?__c=8807034694&authuser=0&__u=7476380198&syncServiceIdentity=true#r.ONLINE&app=cm
 *
 * */