<?php

namespace App\Service;

use App\Model\Lead;
use App\Model\QueryHelper;
use App\Model\SafeMySQL;
use App\Model\CRestBox;
use App\Model\CRestCloud;

class LeadService
{

    public function getLead($classFrom, $leadId)
    {
        $responseArray = QueryHelper::getQuery($classFrom, 'crm.lead.get', [
            'id' => $leadId,
        ])['result'];

        return $this->buildLeadFromResponseArray($responseArray);
    }

    private function buildLeadFromResponseArray(array $responseArray)
    {
        $safeMySQL = new SafeMySQL;

        $lead=[];

        foreach ($responseArray as $key => $value) {
            switch ($key){
                case 'CONTACT_ID':
                    $lead[$key] = $safeMySQL->getRow(
                        "SELECT contact_box FROM det_contact where contact_cloud = ?i", $value)['contact_box'];
                    break;
                case 'UF_CRM_1647594109':
                case 'ASSIGNED_BY_ID':
                    $lead[$key] = $safeMySQL->getRow(
                        "SELECT user_box FROM det_user where user_cloud = ?i", $value[0])['user_box'];
                    break;
                case 'UF_CRM_1650282793':
                case 'UF_CRM_1565700259':
                case 'UF_CRM_1571200098221':
                case 'UF_CRM_1633382622318':
                case 'UF_CRM_1654604584522':
                    $lead[$key] = $this->getFieldListId($key, $value);
                    break;
                case 'STATUS_ID':
                    $lead[$key] = $this->getStatus($value);
                    break;
                case 'SOURCE_ID':
                    $lead[$key] = $safeMySQL
                        ->getRow("SELECT new_value FROM sources where old_value = ?i", $value)['new_value'];
                    break;
                default:
                    $lead[$key] = $value;
            }
        }

        return $lead;
    }

    public function setLead($lead, $classBefore, $sqlLead)
    {
        $safeMySQL = new SafeMySQL;

        $fields = [];
        foreach ($lead as $key => $value) {
            if (!empty($value)) $fields[$key] = $value;
        }

        $methodQuery = QueryHelper::getQuery($classBefore, 'crm.lead.add', [
            'fields' => $fields
        ]);

        $safeMySQL->query($sqlLead, $methodQuery['result'], $lead->getId());
    }

    public function updateLead($lead, $classBefore)
    {
        $fields = [];
        foreach ($lead as $key => $value) {
            if (!empty($value)) $fields[$key] = $value;
        }

        $methodQuery = QueryHelper::getQuery($classBefore, 'crm.lead.update', [
            'id' => $lead->getId(),
            'fields' => $fields
        ]);
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

    public function getStatus($statusCloudId)
    {
        $classFrom = CRestCloud::class;
        $classBefore = CRestBox::class;

        $responseArray = QueryHelper::getQuery($classFrom, 'crm.status.list', [
            'filter' => [
                'STATUS_ID' => $statusCloudId,
                'ENTITY_ID' => 'STATUS'
            ],
        ]);

        return QueryHelper::getQuery($classBefore, 'crm.status.list', [
            'filter' => [
                'NAME' => $responseArray['result'][0]['NAME'],
                'ENTITY_ID' => 'STATUS'
            ],
        ])['result'][0]['STATUS_ID'];
    }
}
