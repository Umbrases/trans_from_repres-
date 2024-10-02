<?php

namespace App\Service;

use App\Model\Deal;
use App\Model\QueryHelper;
use App\Model\SafeMySQL;

class DealService
{
    public function getDeal($classFrom, $dealId, $sqlBeforeId): Deal
    {
        //Запрос информации о сделке
        $responseArrayCloud = QueryHelper::getQuery($classFrom,
            'crm.deal.get', [
                'ID' => $dealId,
            ])['result'];

        return $this->buildDealFromResponseArray($responseArrayCloud);
    }

    public function buildDealFromResponseArray(array $responseArray)
    {
        $deal = [];

        foreach ($responseArray as $key => $value) {
            $deal[$key] = $value;
            $deal[$key] = $this->getFieldListId($key, $value);
        }

        return $deal;
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

    public function setDeal($webHookUrlFrom, Deal $deal, $dealId): void
    {
        $ufaAdd = $this->queryHelper->getQueryBatch($webHookUrlFrom, [
            'contact' => [
                'method' => 'crm.contact.add',
                'params' => [
                    'fields' => [
                        'NAME' => $deal->getContact()->getName(),
                        'SECOND_NAME' => $deal->getContact()->getSecondName(),
                        'LAST_NAME' => $deal->getContact()->getLastName(),
                        'PHONE' => [[
                            'VALUE' => $deal->getContact()->getPhoneValue(),
                            'VALUE_TYPE' => $deal->getContact()->getPhoneValueType(),
                        ]],
                        'BIRTHDATE' => $deal->getContact()->getBirthdate(),
                        'ADDRESS' => $deal->getContact()->getAddress(),
                        'EMAIL' => [[
                            'VALUE' => $deal->getContact()->getEmailValue(),
                            'VALUE_TYPE' => $deal->getContact()->getEmailValueType(),
                        ]],
                        'UF_CRM_629A1B699D519' => $deal->getContact()->getCity(),
                        'UF_CRM_629F51D7AE750' => $deal->getContact()->getPassportSerial(),
                        'UF_CRM_629F51D7F1D30' => $deal->getContact()->getPassportNumber(),
                        'UF_CRM_629F51D834666' => $deal->getContact()->getIssueAt(),
                        'UF_CRM_629F51D85F1A7' => $deal->getContact()->getIssuer(),
                    ]
                ]
            ],
            'deal' => [
                'method' => 'crm.deal.add',
                'params' => [
                    'fields' => [
                        'TITLE' => $deal->getTitle(),
                        'CONTACT_ID' => '$result[contact]',
                        'CATEGORY_ID' => 58,
                        'ASSIGNED_BY_ID' => 208,
                        'UF_CRM_1701760298' => 1,
                        'UF_CRM_1653545949629' => $deal->getContact()->getCity(),
                        'COMMENTS' => $deal->getComments(),
                        'UF_CRM_5D53E58571DB8' => $deal->getSummaDebt(),
                        'UF_CRM_1627447542' => $deal->getNumberDeal(),
                        'UF_CRM_1650372775123' => $deal->getJudgeFio(),
                        'UF_CRM_1654154788530' => $deal->getDateCourt(),
                        'UF_CRM_625D560433A58' => 6182,
                        'UF_CRM_1621386904' => 1,
                        'TYPE_ID' => 'UC_M0M7LA',
                        'SOURCE_ID' => 'UC_5IIS3U',
                    ]
                ]
            ],
        ]);

        //Запись в бд id сделок
        $sql_task = "INSERT INTO det_deal SET deal_tula = ?i, deal_ufa = ?i";
        $this->safeMySQL->query($sql_task, (int)$dealId, (int)$ufaAdd['result']['result']['deal']);
    }

    public function updateDeal($webHookUrlFrom, $sqlDealId, $stage)
    {
        //Обновление информации о сделке
        return $this->queryHelper->getQuery($webHookUrlFrom, 'crm.deal.update',[
            'ID' => $sqlDealId,
            'fields' => [
                'STAGE_ID' => $stage,
            ],]);
    }

}
