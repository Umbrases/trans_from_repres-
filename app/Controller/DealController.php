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

        writeToLog($_REQUEST);

        $dealId = (int)$_REQUEST['deal_id'];
        $this->transferDeal->saveDeal($dealId);
    }
}

function writeToLog($data) {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}