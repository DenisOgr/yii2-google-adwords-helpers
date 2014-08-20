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

    public function  __construct($account, $campaign, $group){

        $this->account  = $account;
        $this->campaign = $campaign;
        $this->group    = $group;

    }

}
