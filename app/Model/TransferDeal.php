<?php

namespace App\Model;

use App\Service\DealService;
use SafeMySQL;

class TransferDeal
{

    private QueryHelper $queryHelper;

    private SafeMySQL $safeMySQL;
    private  DealService $dealService;

    public function __construct()
    {
        $this->queryHelper = new QueryHelper;
        $this->safeMySQL = new SafeMySQL;
        $this->dealService = new DealService;
    }

    public function setDeal($dealId) {
        $crmDealContactGet = $this->dealService->getDealContact($dealId);

        if(!empty($_REQUEST['stage'])) {
            $sqlDealId = $this->safeMySQL->getRow(
                "SELECT deal_ufa FROM det_deal where deal_tula = ?i",
                (int)$dealId
            );

            if(empty($sql_deal_id)) return;
            if($_REQUEST['stage'] == 'rd'){
                $stage = 'C58:PREPARATION';
            } elseif($_REQUEST['stage'] == 'ri'){
                $stage = 'C58:PREPAYMENT_INVOIC';
            }

            $this->dealService->updateDeal($sqlDealId, $stage);
        }

        $date = explode(' ', $crmDealContactGet->getContactArr()['UF_CRM_6333543A28B78']);

        foreach($date as $value){
            $time = strtotime($value);
            if($time == true){
                $time = $value;
                unset($date[array_search($time, $date)]);
            }
        }

        $date = implode(' ', $date);


    }
}