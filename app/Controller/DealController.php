<?php

namespace App\Controller;

use App\Model\CRestBox;
use App\Model\CRestCloud;
use App\Model\Deal;

class DealController
{
    private Deal $deal;

    public function __construct()
    {
        $this->deal = new Deal;
    }

    public function store($dealId) {
        $classFrom = CRestCloud::class;
        $classBefore = CRestBox::class;

        $this->deal->saveDeal($dealId, $classFrom, $classBefore);
    }

    public function observerStore($dealId, $observers)
    {
        $classBefore = CRestBox::class;

        $this->deal->saveObserverDeal($dealId, $observers, $classBefore);
    }
}
