<?php

namespace App\Service;

use App\Model\Contact;
use App\Model\QueryHelper;
use App\Model\SafeMySQL;

class ContactService
{
    public function getContact($classFrom, $contactId, $sqlBeforeId, $classBefore)
    {
        $responseArrayCloud = QueryHelper::getQuery($classFrom,
            'crm.contact.get', [
                'ID' => $contactId,
            ]);

        if (empty($sqlBeforeId)) {
            return $this->buildContactFromResponseArray($responseArrayCloud['result']);
        } else {
            $comparisonResult = $this->comparisonContact($responseArrayCloud['result'], $classBefore, $sqlBeforeId);
            return $this->buildContactFromResponseArray($comparisonResult);
        }
    }

    public function buildContactFromResponseArray(array $responseArrayCloud) :Contact
    {
        $contact = new Contact();

        $contact->setId($responseArrayCloud['ID']);
        array_key_exists('NAME', $responseArrayCloud) ??
            $contact->setName($responseArrayCloud['NAME']);
        array_key_exists('UF_CRM_629A1B699D519', $responseArrayCloud) ??
            $contact->setCity($responseArrayCloud['UF_CRM_629A1B699D519']);
        array_key_exists('SECOND_NAME', $responseArrayCloud) ??
            $contact->setLastName($responseArrayCloud['SECOND_NAME']);
        array_key_exists('LAST_NAME', $responseArrayCloud) ??
            $contact->setSecondName($responseArrayCloud['LAST_NAME']);
        !empty($responseArrayCloud['PHONE'][0]['VALUE']) ??
            $contact->setPhoneValue($responseArrayCloud['PHONE'][0]['VALUE']);
        !empty($responseArrayCloud['PHONE'][0]['VALUE_TYPE']) ??
            $contact->setPhoneValueType($responseArrayCloud['PHONE'][0]['VALUE_TYPE']);
        array_key_exists('BIRTHDATE', $responseArrayCloud) ??
            $contact->setBirthdate($responseArrayCloud['BIRTHDATE']);
        array_key_exists('ADDRESS', $responseArrayCloud) ??
            $contact->setAddress($responseArrayCloud['ADDRESS']);
        !empty($responseArrayCloud['EMAIL'][0]['VALUE']) ??
            $contact->setEmailValue($responseArrayCloud['EMAIL'][0]['VALUE']);
        !empty($responseArrayCloud['EMAIL'][0]['VALUE_TYPE']) ??
            $contact->setEmailValueType($responseArrayCloud['EMAIL'][0]['VALUE_TYPE']);
        array_key_exists('UF_CRM_629F51D7AE750', $responseArrayCloud) ??
            $contact->setPassportSerial($responseArrayCloud['UF_CRM_629F51D7AE750']);
        array_key_exists('UF_CRM_629F51D7F1D30', $responseArrayCloud) ??
            $contact->setPassportNumber($responseArrayCloud['UF_CRM_629F51D7F1D30']);
        array_key_exists('UF_CRM_629F51D834666', $responseArrayCloud) ??
            $contact->setDateIssue($responseArrayCloud['UF_CRM_629F51D834666']);
        array_key_exists('UF_CRM_629F51D88AC70', $responseArrayCloud) ??
            $contact->setDepartmentCode($responseArrayCloud['UF_CRM_629F51D88AC70']);
        array_key_exists('LEAD_ID', $responseArrayCloud) ??
            $contact->setLeadId($responseArrayCloud['LEAD_ID']);
writeToLog($contact);
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
                'UF_CRM_629F51D834666' => $contact->getDateIssue(),
                'UF_CRM_629F51D88AC70' => $contact->getDepartmentCode(),
                'UF_CRM_629F51D85F1A7' => $contact->getIssuer(),
                'LEAD_ID' => $contact->getLeadId(),
            ]
        ]);

        $safeMySQL->query($sqlContact, $methodQuery['result'], $contact->getId());
    }

    public function comparisonContact($responseArrayCloud, $classBefore, $sqlBeforeId)
    {
        $responseArrayBox = QueryHelper::getQuery($classBefore,
            'crm.contact.list', [
                'filter' =>[
                    'ID' => $sqlBeforeId,
                ],
                'select' => [
                    'ID',
                    'UF_CRM_629A1B699D519',
                    'NAME',
                    'SECOND_NAME',
                    'LAST_NAME',
                    'PHONE',
                    'BIRTHDATE',
                    'ADDRESS',
                    'EMAIL',
                    'UF_CRM_629F51D7AE750',
                    'UF_CRM_629F51D7F1D30',
                    'UF_CRM_629F51D834666',
                    'UF_CRM_629F51D85F1A7',
                    'UF_CRM_629F51D88AC70',
                    'LEAD_ID',
                ]
            ]);

        foreach ($responseArrayCloud as $keyCloud => $valueCloud) {
            foreach ($responseArrayBox['result'][0] as $keyBox => $valueBox) {
                if ($keyCloud != $keyBox) continue;
                if ($valueBox != $valueCloud) $response[$keyCloud] = $valueCloud;
            }
        }
        return $response;
    }

    public function updateContact(Contact $contact, $classBefore, $sqlUpdateContact, $sqlBeforeId)
    {
        $safeMySQL = new SafeMySQL;

        $methodQuery = QueryHelper::getQuery($classBefore, 'crm.contact.update', [
            'id' => $sqlBeforeId,
            'fields' => [
                !empty($contact->getName()) ?? 'NAME' => $contact->getName(),
                !empty($contact->getSecondName()) ?? 'SECOND_NAME' => $contact->getSecondName(),
                !empty($contact->getLastName()) ?? 'LAST_NAME' => $contact->getLastName(),
                !empty($contact->getPhoneValue()) ?? 'PHONE' => [[
                    'VALUE' => $contact->getPhoneValue(),
                    'VALUE_TYPE' => $contact->getPhoneValueType(),
                ]],
                !empty($contact->getBirthdate()) ?? 'BIRTHDATE' => $contact->getBirthdate(),
                !empty($contact->getAddress()) ?? 'ADDRESS' => $contact->getAddress(),
                !empty($contact->getEmailValue()) ?? 'EMAIL' => [[
                    'VALUE' => $contact->getEmailValue(),
                    'VALUE_TYPE' => $contact->getEmailValueType(),
                ]],
                !empty($contact->getCity()) ?? 'UF_CRM_629A1B699D519' => $contact->getCity(),
                !empty($contact->getPassportSerial()) ?? 'UF_CRM_629F51D7AE750' => $contact->getPassportSerial(),
                !empty($contact->getPassportNumber()) ?? 'UF_CRM_629F51D7F1D30' => $contact->getPassportNumber(),
                !empty($contact->getDateIssue()) ?? 'UF_CRM_629F51D834666' => $contact->getDateIssue(),
                !empty($contact->getDepartmentCode()) ?? 'UF_CRM_629F51D88AC70' => $contact->getDepartmentCode(),
                !empty($contact->getIssuer()) ?? 'UF_CRM_629F51D85F1A7' => $contact->getIssuer(),
                !empty($contact->getLeadId()) ?? 'LEAD_ID' => $contact->getLeadId(),
            ]
        ]);
writeToLog($sqlBeforeId);
writeToLog($methodQuery);
        $safeMySQL->query($sqlUpdateContact, $methodQuery['result'], $contact->getId());
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