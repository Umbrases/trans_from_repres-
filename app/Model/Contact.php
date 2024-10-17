<?php

namespace App\Model;

use App\Service\ContactService;
use App\Controller\LeadController;


class Contact
{
    public function saveContact($classFrom, $classBefore, $contactId)
    {
        $safeMySQL = new SafeMySQL;
        $contactService = new ContactService;

        $sqlBeforeId = $safeMySQL
            ->getRow("SELECT `contact_box` FROM det_contact where contact_cloud = ?i", (int)$contactId)['contact_box'];
        $sqlContact = "INSERT INTO det_contact SET contact_box = ?i, contact_cloud = ?i";

        //Вывод контакта
        $contact = $contactService->getContact($classFrom, $contactId, $sqlBeforeId, $classBefore);

        if (!empty($contact['LEAD_ID'])) {
            $sqlLead = $safeMySQL
                ->getRow("SELECT `lead_box` FROM det_lead where lead_cloud = ?i", $contact['LEAD_ID'])['lead_box'];
            if(empty($sqlLead)) {
                $leadController = new LeadController();
                $leadController->create($contact['LEAD_ID']);
                $sqlLead = $safeMySQL
                    ->getRow("SELECT `lead_box` FROM det_lead where lead_cloud = ?i", $contact['LEAD_ID'])['lead_box'];
            }
        }

        if (empty($sqlBeforeId)) {
            $contactService->setContact($contact, $classBefore, $sqlContact);
        } else {
            $contactService->updateContact($contact, $classBefore, $sqlBeforeId);
        }
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
