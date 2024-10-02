<?php

namespace App\Model;

use App\Service\LeadService;

class Lead
{
    public function __construct()
    {
        $this->leadService = new LeadService;
    }

    public function saveLead($classFrom, $classBefore, $leadId): void
    {
        $safeMySQL = new SafeMySQL;

        $lead = $this->leadService->getLead($classFrom, $leadId);

        $sqlBeforeId = $safeMySQL->getRow("SELECT `lead_box` FROM det_lead where lead_cloud = ?i", (int)$leadId);
        $sqlLead = "INSERT INTO det_lead SET lead_box = ?i , lead_cloud = ?i";

        //Если лид в коробке отсутствует, то создаем лид
        if (empty($sqlBeforeId)){
            $this->leadService->setLead($lead, $classBefore, $sqlLead);
        } else {
            // ... иначе обновляем его
            $this->leadService->updateLead($lead, $classBefore);
        }
    }
}
