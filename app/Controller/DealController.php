<?php

namespace App\Controller;

use App\Model\CRestBox;
use App\Model\CRestCloud;
use App\Model\TransferDeal;

class DealController
{
    private TransferDeal $transferDeal;
    public function __construct()
    {
        $this->transferDeal = new TransferDeal;
    }

    public function store($dealId) {
        $classFrom = CRestCloud::class;
        $classBefore = CRestBox::class;

        $this->transferDeal->saveDeal($dealId, $classFrom, $classBefore);
    }
}
