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
        $responseAdressCloud = QueryHelper::getQuery($classFrom, 'crm.address.list',[
            'filter' => [
                'ENTITY_TYPE_ID' => 3,
                'ENTITY_ID' => $contactId
            ]
        ])['result'];

        if (empty($sqlBeforeId)) {
            return $this->buildContactFromResponseArray($responseArrayCloud['result'], $responseAdressCloud);
        } else {
            $comparisonResult = $this->comparisonContact($responseArrayCloud['result'], $classBefore, $sqlBeforeId);
            return $this->buildContactFromResponseArray($comparisonResult, $responseAdressCloud);
        }
    }

    public function buildContactFromResponseArray(array $responseArrayCloud, $responseAdressCloud)
    {
        $contact = [];
        foreach ($responseArrayCloud as $key => $value) {
            switch ($key) {
                case 'ID' :
                    $contact['ID'] = $value;
                    break;
                case 'UF_CRM_629A1B699D519' :
                    $contact['UF_CRM_629A1B699D519'] = $value;
                    break;
                case 'NAME' :
                    $contact['NAME'] = $value;
                    break;
                case 'SECOND_NAME' :
                    $contact['SECOND_NAME'] = $value;
                    break;
                case 'LAST_NAME' :
                    $contact['LAST_NAME'] = $value;
                    break;
                case 'PHONE' :
                    $contact['PHONE'] = $value;
                    break;
                case 'EMAIL' :
                    $contact['EMAIL'] = $value;
                    break;
                case 'BIRTHDATE' :
                    $contact['BIRTHDATE'] = $value;
                    break;
                case 'ADDRESS' :
                    $contact['ADDRESS'] = $responseAdressCloud;
                    break;
                case 'UF_CRM_5D53E5846CE99' :
                    $contact['UF_CRM_5D53E5846CE99'] = $value;
                    break;
                case 'UF_CRM_1624004832' :
                    $contact['UF_CRM_1624004832'] = $value;
                    writeToLog($value);
                    break;
                case 'UF_CRM_1663670733026' :
                    $contact['UF_CRM_1663670733026'] = $value;
                    break;
                case 'UF_CRM_62CD365DB2DED' :
                    $contact['UF_CRM_62CD365DB2DED'] = $value;
                    break;
                case 'UF_CRM_62CD365D51F74' :
                    $contact['UF_CRM_62CD365D51F74'] = $value;
                    break;
                case 'UF_CRM_62CD365DECFE7' :
                    $contact['UF_CRM_62CD365DECFE7'] = $value;
                    break;
                case 'COMMENTS' :
                    $contact['COMMENTS'] = $value;
                    break;
                case 'UF_CRM_629F51D7AE750' :
                    $contact['UF_CRM_629F51D7AE750'] = $value;
                    break;
                case 'UF_CRM_629F51D7F1D30' :
                    $contact['UF_CRM_629F51D7F1D30'] = $value;
                    break;
                case 'UF_CRM_629F51D834666' :
                    $contact['UF_CRM_629F51D834666'] = $value;
                    break;
                case 'UF_CRM_629F51D85F1A7' :
                    $contact['UF_CRM_629F51D85F1A7'] = $value;
                    break;
                case 'UF_CRM_629F51D88AC70' :
                    $contact['UF_CRM_629F51D88AC70'] = $value;
                    break;
                case 'UF_CRM_1720601597' :
                    $contact['UF_CRM_1720601597'] = $responseArrayCloud['ID'];
                    break;
                case 'LEAD_ID' :
                    $contact['LEAD_ID'] = $value;
                    break;
            }
        }
        $contact['UF_CRM_1721830931'] = 'https://stopzaym.bitrix24.ru/crm/contact/details/'.$responseArrayCloud['ID'].'/?any=details%2F31674%2F';
        $contact['UF_CRM_1722586545'] = 'https://stopzaym.bitrix24.ru/crm/contact/details/'.$responseArrayCloud['ID'].'/?any=details%2F31674%2F';

        return $contact;
    }

    public function setContact($contact, $classBefore, $sqlContact)
    {
        $safeMySQL = new SafeMySQL;

        $fields = [];
        foreach ($contact as $key => $value) {
            if (!empty($value)) $fields[$key] = $value;
        }

        writeToLog($contact);
        $methodQuery = QueryHelper::getQuery($classBefore, 'crm.contact.add', [
            'fields' => $fields
        ]);

        foreach ($contact['ADDRESS'] as $keyAddress => $valueAddress) {
            (QueryHelper::getQuery($classBefore, 'crm.address.add', [
                'fields' => [
                    'TYPE_ID' => 1,
                    'ENTITY_TYPE_ID' => 3,
                    'ENTITY_ID' => $methodQuery['result'],
                    'PROVINCE' => $contact['ADDRESS'][$keyAddress]['PROVINCE'],
                    'COUNTRY' => $contact['ADDRESS'][$keyAddress]['COUNTRY'],
                    'CITY' => $contact['ADDRESS'][$keyAddress]['CITY'],
                    'ADDRESS_1' => $contact['ADDRESS'][$keyAddress]['ADDRESS_1'],
                    ]
            ]));
        }
        $safeMySQL->query($sqlContact, $methodQuery['result'], $contact['ID']);
    }

    public function comparisonContact($responseArrayCloud, $classBefore, $sqlBeforeId)
    {
        $responseArrayBox = QueryHelper::getQuery($classBefore,
            'crm.contact.get', [
                    'ID' => $sqlBeforeId,
            ]);

        if (!array_key_exists('PHONE', $responseArrayBox['result'])) $responseArrayBox['result']['PHONE'][0] = null;
        if (!array_key_exists('EMAIL', $responseArrayBox['result'])) $responseArrayBox['result']['EMAIL'][0] = null;

        $response = [];
        $responsePhone = [];
        $responseEmail = [];
        foreach ($responseArrayCloud as $keyCloud => $valueCloud) {
            foreach ($responseArrayBox['result'] as $keyBox => $valueBox) {
                if ($keyCloud === $keyBox
                    && $valueBox != $valueCloud
                    && $keyCloud != 'EMAIL'
                    && $keyCloud != 'PHONE') $response[$keyCloud] = $valueCloud;

                if ($keyCloud === $keyBox && $keyCloud == 'PHONE'){
                    foreach ($valueCloud as $keyPhoneCloud => $valuePhoneCloud){
                        foreach ($valueBox as $keyPhoneBox => $valuePhoneBox) {
                            if ($valuePhoneCloud['VALUE'] != $valuePhoneBox['VALUE'] || empty($valuePhoneBox)) {
                                $responsePhone[$keyPhoneCloud]['VALUE'] = $valuePhoneCloud['VALUE'];
                                $responsePhone[$keyPhoneCloud]['VALUE_TYPE'] = $valuePhoneCloud['VALUE_TYPE'];
                            }
                        }
                    }
                }
                if ($keyCloud === $keyBox && $keyCloud == 'EMAIL'){
                    foreach ($valueCloud as $keyEmailCloud => $valueEmailCloud){
                        foreach ($valueBox as $keyPhoneBox => $valueEmailBox) {
                            if ($valueEmailCloud['VALUE'] != $valueEmailBox['VALUE']) {
                                $responseEmail[$keyEmailCloud]['VALUE'] = $valueEmailCloud['VALUE'];
                                $responseEmail[$keyEmailCloud]['VALUE_TYPE'] = $valueEmailCloud['VALUE_TYPE'];
                            }
                        }
                    }
                }
            }
        }
        $response['PHONE'] = $responsePhone;
        $response['EMAIL'] = $responseEmail;

        return $response;
    }

    public function updateContact($contact, $classBefore, $sqlBeforeId)
    {
        $fields = [];
        foreach ($contact as $key => $value) {
            if (!empty($value)) $fields[$key] = $value;
        }
writeToLog($contact);
        QueryHelper::getQuery($classBefore, 'crm.contact.update', [
            'id' => $sqlBeforeId,
            'fields' => $fields
        ]);
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