<?php

namespace App\Controller;

use App\Model\TransferDeal;

class DealController
{
    private TransferDeal $transferDeal;
    public function __construct()
    {
        $this->transferDeal = new TransferDeal;
    }

    public function index() {
        if (empty($_REQUEST['DOMAIN']) && $_REQUEST['DOMAIN'] != 'b24-e77y0j.bitrix24.ru') {
            return;
        }

        $dealId = (int)$_REQUEST['deal_id'];
        $this->transferDeal->saveDeal($dealId);
    }
}
