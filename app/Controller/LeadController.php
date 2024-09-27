<?php

namespace App\Controller;

use App\Model\Lead;
use App\Model\CRestBox;
use App\Model\CRestCloud;

class LeadController
{
    private Lead $lead;
    public function __construct()
    {
        $this->lead = new Lead;
    }

    public function create($leadId) {
        $classFrom = CRestCloud::class;
        $classBefore = CRestBox::class;

        $this->lead->setLead($classFrom, $classBefore, $leadId);
    }
}
