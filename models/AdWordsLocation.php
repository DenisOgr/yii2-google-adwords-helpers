<?php
/**
 * User: Denis Porplenko <denis.porplenko@gmail.com>
 * Date: 14.08.14
 * Time: 9:43
 */

namespace denisog\gah\models;

class AdWordsLocation {

    public $account;
    public $campaign;
    public $group;
    public $clientNumber;

    /**
     * @param $account
     * @param $campaign
     * @param $group
     * @param null $clientNumber
     */
    public function  __construct($account, $campaign, $group, $clientNumber = null){

        $this->account         = $account;
        $this->campaign        = $campaign;
        $this->group           = $group;
        $this->clientNumber    = $clientNumber;

    }

}
