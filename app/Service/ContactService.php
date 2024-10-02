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
            return $this->buildContactFromResponseArray($responseArrayCloud['result'], $responseAdressCloud, $classBefore);
        } else {
            $comparisonResult = $this->comparisonContact($responseArrayCloud['result'], $classBefore, $sqlBeforeId);
            return $this->buildContactFromResponseArray($comparisonResult, $responseAdressCloud, $classBefore);
        }
    }

    public function buildContactFromResponseArray(array $responseArrayCloud, $responseAdressCloud, $classBefore)
    {
        $contact = [];
        foreach ($responseArrayCloud as $key => $value) {
            switch ($key) {
                case 'ADDRESS' :
                    $contact['ADDRESS'] = $responseAdressCloud;
                    break;
                case 'UF_CRM_1720601597' :
                    $contact['UF_CRM_1720601597'] = $responseArrayCloud['ID'];
                    break;
                case 'UF_CRM_5D53E5846CE99' :
                    $contact['UF_CRM_5D53E5846CE99'] = $this->getFieldListId('UF_CRM_5D53E5846CE99', $value);
                    break;
                case 'UF_CRM_5D53E5845A238' :
                    $contact['UF_CRM_5D53E5845A238'] = $this->getFieldListId('UF_CRM_5D53E5845A238', $value);
                    break;
                default:
                    $contact[$key] = $value;
            }
        }
        $contact['UF_CRM_1721830931'] = 'https://stopzaym.bitrix24.ru/crm/contact/details/'.$responseArrayCloud['ID'].'/?any=details%2F31674%2F';

        if (empty($contact['NAME'])) {
            if (empty($contact['LAST_NAME'])){
                $contactDiskFolder  = 'Contact ' . $contact['ID']; //Название папки контакта
            } else {
                $contactDiskFolder = 'Contact ' . $contact['ID'] . ' (' . $contact['LAST_NAME'] . ')'; //Название папки контакта
            }
        } else {
            if (empty($contact['LAST_NAME'])){
                $contactDiskFolder = 'Contact ' . $contact['ID'] . ' (' . $contact['NAME'] . ')'; //Название папки контакта
            } else {
                $contactDiskFolder = 'Contact ' . $contact['ID'] . ' (' . $contact['NAME'] . ' ' . $contact['LAST_NAME'] . ')'; //Название папки контакта
            }
        }

        $contact['UF_CRM_1722586545'] = $this->issetContactDiskFolder($classBefore, $contactDiskFolder);

        return $contact;
    }

    public function setContact($contact, $classBefore, $sqlContact)
    {
        $safeMySQL = new SafeMySQL;

        $fields = [];
        foreach ($contact as $key => $value) {
            if (!empty($value)) $fields[$key] = $value;
        }

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

        $response = [];
        $responsePhone = [];
        $responseEmail = [];

        if (!array_key_exists('PHONE', $responseArrayBox['result'])) {
            $responseArrayBox['result']['PHONE'] = $responseArrayCloud['PHONE'];
        } else {
            foreach ($responseArrayCloud['PHONE'] as $keyPhoneCloud => $valuePhoneCloud){
                foreach ($responseArrayBox['result']['PHONE'] as $keyPhoneBox => $valuePhoneBox) {
                    if ($valuePhoneCloud['VALUE'] != $valuePhoneBox['VALUE'] || empty($valuePhoneBox)) {
                        $responsePhone[$keyPhoneCloud]['VALUE'] = $valuePhoneCloud['VALUE'];
                        $responsePhone[$keyPhoneCloud]['VALUE_TYPE'] = $valuePhoneCloud['VALUE_TYPE'];
                    }
                }
            }
        }
        if (!array_key_exists('EMAIL', $responseArrayBox['result'])) {
            $responseArrayBox['result']['EMAIL'] = $responseArrayCloud['EMAIL'];
        } else {
            foreach ($responseArrayCloud['EMAIL'] as $keyEmailCloud => $valueEmailCloud){
                foreach ($responseArrayBox['result']['EMAIL'] as $keyEmailBox => $valueEmailBox) {
                    if ($valueEmailCloud['VALUE'] != $valueEmailBox['VALUE']) {
                        $responseEmail[$keyEmailCloud]['VALUE'] = $valueEmailCloud['VALUE'];
                        $responseEmail[$keyEmailCloud]['VALUE_TYPE'] = $valueEmailCloud['VALUE_TYPE'];
                    }
                }
            }
        }

        foreach ($responseArrayCloud as $keyCloud => $valueCloud) {
            foreach ($responseArrayBox['result'] as $keyBox => $valueBox) {
                if ($keyCloud === $keyBox
                    && $valueBox != $valueCloud
                    && $keyCloud != 'EMAIL'
                    && $keyCloud != 'PHONE') $response[$keyCloud] = $valueCloud;
            }
        }
        $response['PHONE'] = $responsePhone;
        $response['EMAIL'] = $responseEmail;

        return $response;
    }

    public function getFieldListId($ufCrm, $fieldId)
    {
        $safeMySQL = new SafeMySQL;

        if (!empty($fieldId)) {
            $field = $safeMySQL->getRow(
                "SELECT * FROM user_fields where field_name 
                = ?s", $ufCrm);
            $jsonArrayCloud = json_decode($field['cloud_list']);
            $jsonArrayBox = json_decode($field['box_list']);

            foreach ($jsonArrayCloud as $keyCloud => $valueCloud) {
                if ($valueCloud->ID == $fieldId) {
                    foreach ($jsonArrayBox as $keyBox => $valueBox) {
                        if ($valueBox->VALUE == $valueCloud->VALUE) {
                            return $valueBox->ID;
                        }}}}
        }
    }

    function issetContactDiskFolder($classBefore, $contactDiskFolder){

        $folder = QueryHelper::getQuery($classBefore,'disk.folder.getchildren', [
            'id' => 403,
            'filter' => [
                'NAME' => $contactDiskFolder
            ]
        ]);

        if (empty($folder['result'])){
            $folder_add = QueryHelper::getQuery($classBefore,'disk.folder.addsubfolder', [
                'id' => 403,
                'data' => [
                    'NAME' => $contactDiskFolder //Вытянуть имя папки
                ]
            ]);

            $folder = $folder_add['result'];
        } else {
            $folder = $folder['result'][0];
        }


        return $folder['DETAIL_URL'];
    }

    public function updateContact($contact, $classBefore, $sqlBeforeId)
    {
        $fields = [];
        foreach ($contact as $key => $value) {
            if (!empty($value)) $fields[$key] = $value;
        }

        QueryHelper::getQuery($classBefore, 'crm.contact.update', [
            'id' => $sqlBeforeId,
            'fields' => $fields
        ]);
    }

}

