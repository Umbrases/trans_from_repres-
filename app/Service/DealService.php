<?php

namespace App\Service;

use App\Model\Deal;
use App\Model\QueryHelper;
use App\Model\SafeMySQL;
use App\Controller\ContactController;
use App\Controller\LeadController;


class DealService
{
    public function getDeal($classFrom, $dealId, $sqlBeforeId, $classBefore)
    {
        //Запрос информации о сделке
        $responseArrayCloud = QueryHelper::getQuery($classFrom,
            'crm.deal.get', [
                'ID' => $dealId,
            ])['result'];

        if (empty($sqlBeforeId)) {
            return $this->buildDealFromResponseArray($responseArrayCloud);
        } else {
            $comparisonResult = $this->comparisonDeal($responseArrayCloud, $classBefore, $sqlBeforeId);
            return $this->buildDealFromResponseArray($comparisonResult);
        }

    }

    public function buildDealFromResponseArray(array $responseArray)
    {
        $safeMySQL = new SafeMySQL;

        $deal = [];
        unset($responseArray['UF_CRM_CS_DEAL_MAGNET']);
        unset($responseArray['UF_CRM_CS_DEAL_CONTACT']);

        foreach ($responseArray as $key => $value) {
            $field = $safeMySQL->getRow(
                "SELECT * FROM user_fields where field_name 
                = ?s", $key);

            if (empty($field['box_list'])) {
                if (!empty($value)) {
                    switch ($key) {
                        case 'CREATED_BY_ID':
                        case 'ASSIGNED_BY_ID':
                        case 'UF_CRM_60A39EE28B65D':
                        case 'UF_CRM_6090D63B8E837':
                        case 'UF_CRM_60928641122F4':
                        case 'UF_CRM_6094E0E6D2B1D':
                        case 'UF_CRM_6094E0E726214':
                        case 'UF_CRM_1650611983':
                            $deal[$key] = $safeMySQL
                                ->getRow("SELECT `new_id` FROM users where `old_id` = ?i", $value)['user_box'];
                            break;
                        case 'UF_CRM_1721830990' :
                            $deal[$key] = 'https://stopzaym.bitrix24.ru/crm/deal/details/' . $responseArray['ID'];
                            break;
                        case 'CONTACT_ID':
                            $deal[$key] = $safeMySQL
                                ->getRow("SELECT `new_id` FROM contacts where `old_id` = ?i", $value)['new_id'];
                            if (empty($deal[$key])){
                                $contactController = new ContactController();
                                $contactController->store($value);
                            }
                            break;
                        case 'LEAD_ID':
                            $deal[$key] = $safeMySQL
                                ->getRow("SELECT `new_id` FROM leads where `old_id` = ?i", $value)['new_id'];
                            if (empty($deal[$key])){
                                $leadController = new LeadController();
                                $leadController->create($value);
                            }
                            break;
                        case 'CATEGORY_ID':
                            $deal[$key] = $safeMySQL->getRow("SELECT `new_category_id` FROM stages where `old_category_id` = ?i", $value)['new_category_id'];
                            break;
                        case 'STAGE_ID':
                            $deal[$key] = $safeMySQL->getRow("SELECT `new_status_id` FROM stages where `old_status_id` = ?s", $value)['new_status_id'];
                            break;
                        default:
                            $deal[$key] = $value;
                    }
                }
            }else {
                $deal[$key] = $this->getFieldListId($key, $value);
            }
            //writeToLog($deal);
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

    public function comparisonDeal($responseArrayCloud, $classBefore, $sqlBeforeId)
    {
        $responseArrayBox = QueryHelper::getQuery($classBefore,
            'crm.deal.get', [
                'ID' => $sqlBeforeId,
            ]);

        $response = [];

        foreach ($responseArrayCloud as $keyCloud => $valueCloud) {
            foreach ($responseArrayBox['result'] as $keyBox => $valueBox) {
                if ($keyCloud === $keyBox && $valueBox != $valueCloud) $response[$keyCloud] = $valueCloud;
            }
        }

        return $response;
    }


    public function setDeal($classBefore, $deal, $sqlDeal)
    {
        $safeMySQL = new SafeMySQL;

//        writeToLog($deal);
        $fields = [];
        foreach ($deal as $key => $value) {
            if (!empty($value)) $fields[$key] = $value;
        }

        $methodQuery = QueryHelper::getQuery($classBefore, 'crm.deal.add', [
            'fields' => $fields
        ]);

        //Запись в бд id сделок
        $safeMySQL->query($sqlDeal, $methodQuery['result'], $deal['ID']);
    }

    public function updateDeal($classBefore, $deal, $sqlBeforeId)
    {
        $safeMySQL = new SafeMySQL;

        $fields = [];
        foreach ($deal as $key => $value) {
            if (!empty($value)) $fields[$key] = $value;
        }

        QueryHelper::getQuery($classBefore, 'crm.deal.update', [
            'id' => $sqlBeforeId,
            'fields' => $fields
        ]);

    }

    public function setObserverDeal($classBefore, $observerBox, $sqlBeforeId)
    {
        QueryHelper::getQuery($classBefore, 'crm.deal.update', [
            'id' => $sqlBeforeId,
            'fields' => [
                'UF_CRM_1728898409' => $observerBox,
            ]
        ]);
    }

}

function writeToLog($data)
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}