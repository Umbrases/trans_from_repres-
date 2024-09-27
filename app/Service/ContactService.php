<?php

namespace App\Service;

use App\Model\QueryHelper;
use App\Model\SafeMySQL;

class ContactService
{
    public function getContact($classFrom, $contactId)
    {
        $columnResponsibleId = QueryHelper::getQuery($classFrom,
            'crm.contact.get', [
                'ID' => $contactId,
            ]);
        writeToLog($columnResponsibleId);
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