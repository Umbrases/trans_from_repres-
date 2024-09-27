<?php

namespace App\Service;

use App\Model\Contact;
use App\Model\QueryHelper;
use App\Model\SafeMySQL;

class ContactService
{
    public function getContact($classFrom, $contactId)
    {
        $responseArray = QueryHelper::getQuery($classFrom,
            'crm.contact.get', [
                'ID' => $contactId,
            ]);

        return $this->buildContactFromResponseArray($responseArray);
    }

    public function buildContactFromResponseArray(array $responseArray) :Contact
    {
        $contact = new Contact();

        $contact->setId($responseArray['result']['ID']);
        $contact->setCity($responseArray['result']['UF_CRM_629A1B699D519']);
        $contact->setName($responseArray['result']['NAME']);
        $contact->setLastName($responseArray['result']['SECOND_NAME']);
        $contact->setSecondName($responseArray['result']['LAST_NAME']);
        $contact->setPhoneValue($responseArray['result']['PHONE'][0]['VALUE'] ?? '');
        $contact->setPhoneValueType($responseArray['result']['PHONE'][0]['VALUE_TYPE'] ?? '');
        $contact->setBirthdate($responseArray['result']['BIRTHDATE']);
        $contact->setAddress($responseArray['result']['ADDRESS']);
        $contact->setEmailValue($responseArray['result']['EMAIL'][0]['VALUE'] ?? '');
        $contact->setEmailValueType($responseArray['contact']['EMAIL'][0]['VALUE_TYPE'] ?? '');
        $contact->setPassportSerial($responseArray['result']['UF_CRM_629F51D7AE750']);
        $contact->setPassportNumber($responseArray['result']['UF_CRM_629F51D7F1D30']);
        $contact->setDateIssue($responseArray['result']['UF_CRM_629F51D834666']);
        $contact->setIssuer($responseArray['result']['UF_CRM_629F51D85F1A7']);
        $contact->setDepartmentCode($responseArray['result']['UF_CRM_629F51D88AC70']);
        $contact->setLeadId($responseArray['result']['LEAD_ID']);

        return $contact;
    }

    public function setContact(Contact $contact, $classBefore, $sqlContact)
    {
        $safeMySQL = new SafeMySQL;

        $methodQuery = QueryHelper::getQuery($classBefore, 'crm.contact.add', [
            'fields' => [
                'NAME' => $contact->getName(),
                'SECOND_NAME' => $contact->getSecondName(),
                'LAST_NAME' => $contact->getLastName(),
                'PHONE' => [[
                    'VALUE' => $contact->getPhoneValue(),
                    'VALUE_TYPE' => $contact->getPhoneValueType(),
                ]],
                'BIRTHDATE' => $contact->getBirthdate(),
                'ADDRESS' => $contact->getAddress(),
                'EMAIL' => [[
                    'VALUE' => $contact->getEmailValue(),
                    'VALUE_TYPE' => $contact->getEmailValueType(),
                ]],
                'UF_CRM_629A1B699D519' => $contact->getCity(),
                'UF_CRM_629F51D7AE750' => $contact->getPassportSerial(),
                'UF_CRM_629F51D7F1D30' => $contact->getPassportNumber(),
                'UF_CRM_629F51D834666' => $contact->getIssueAt(),
                'UF_CRM_629F51D85F1A7' => $contact->getIssuer(),
            ]
        ]);

        $safeMySQL->query($sqlContact, $methodQuery['result'], $contact->getId());
    }

    public function updateContact()
    {

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