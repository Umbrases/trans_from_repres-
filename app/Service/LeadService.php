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
            'select' => [
                'ID',
                'NAME', // Имя
                'LAST_NAME', // Фамилия
                'SECOND_NAME', // Отчество
                'PHONE', // Телефон
                'CONTACT_ID', //контакт
                'UF_CRM_1647594109', //оператор кц
                'UF_CRM_1650282793', //подразделение
                'COMMENTS', //Комментарий
                'UF_CRM_1654253931573', // Город
                'UF_CRM_1565700259', //Город представительство
                'SOURCE_ID', // Источник
                'STATUS_ID', // Статус
                'ASSIGNED_BY_ID', //Ответственный
                'UF_CRM_1620044630', //Дата и время встречи
//                'OBSERVER', //Наблюдатели
                'UF_CRM_1571200098221', //Услуга
                'UF_CRM_1633382622318', //Отказы КЦ
                'UF_CRM_1654604584522', //Тип услуги
                'UF_CRM_1669293769', //Кредиторы и суммы долга
                'UF_CRM_1669293853', //Источники дохода
                'UF_CRM_1657769573776',  //Семейной положение
                'UF_CRM_1669293874', //Имущество
                'UF_CRM_1669287731', //Сделки за последние 3 года
                'UF_CRM_1669287789', //Предоставление недостоверных данных кредиторам
                'UF_CRM_1669287861', //Уголовная ответственность
                'UF_CRM_1669287897', //Сохранение имущества
                'UF_CRM_1669292595', //Предмет Общего договора
                'UF_CRM_1657784480387', //Кредитная организация или лицо, выдавшие кредит
                'UF_CRM_CITY', //Город рефенансирование
            ]
        ]);

        return $this->buildLeadFromResponseArray($responseArray);
    }

    private function buildLeadFromResponseArray(array $responseArray)
    {
        $lead = new Lead();
        $safeMySQL = new SafeMySQL;

        $lead->setId($responseArray['result']['ID']);
        $lead->setName($responseArray['result']['NAME']);
        $lead->setLastName($responseArray['result']['LAST_NAME']);
        $lead->setSecondName($responseArray['result']['SECOND_NAME']);
        $lead->setContactId(
            !empty($responseArray['result']['CONTACT_ID']) ? $safeMySQL->getRow(
                "SELECT contact_box FROM det_contact where contact_cloud = ?i",
                $responseArray['result']['CONTACT_ID']
            )['user_box'] : null
        );
        $lead->setPhone($responseArray['result']['PHONE'][0]['VALUE']);
        $lead->setTypePhone($responseArray['result']['PHONE'][0]['VALUE_TYPE']);
        $lead->setOperator(
            !empty($responseArray['result']['UF_CRM_1647594109']) ? $safeMySQL->getRow(
                "SELECT user_box FROM det_user where user_cloud = ?i",
                $responseArray['result']['UF_CRM_1647594109'][0])['user_box'] : null
        );
        $lead->setDivisions($this->getFieldListId(
            'UF_CRM_1650282793',
            $responseArray['result']['UF_CRM_1650282793']));
        $lead->setComments($responseArray['result']['COMMENTS']);
        $lead->setCity($responseArray['result']['UF_CRM_1654253931573']);
        $lead->setAgency($this->getFieldListId(
            'UF_CRM_1565700259',
            $responseArray['result']['UF_CRM_1565700259']));
        $lead->setSourceId(
            !empty($responseArray['result']['SOURCE_ID']) ? $safeMySQL->getRow(
                "SELECT new_value FROM sources where old_value = ?i",
                $responseArray['result']['SOURCE_ID']
            )['new_value'] : null);
        $lead->setStatusId($this->getStatus($responseArray['result']['STATUS_ID']));
        $lead->setAssignedById(
            !empty($responseArray['result']['ASSIGNED_BY_ID']) ? $safeMySQL->getRow(
                "SELECT user_box FROM det_user where user_cloud = ?i",
                $responseArray['result']['ASSIGNED_BY_ID']
            )['user_box'] : 1
        );
        $lead->setDateMeeting($responseArray['result']['UF_CRM_1620044630']);
//        $lead->setObserver($responseArray['result']['']);
        $lead->setService($this->getFieldListId(
            'UF_CRM_1571200098221',
            $responseArray['result']['UF_CRM_1571200098221']));
        $lead->setRefusals($this->getFieldListId(
            'UF_CRM_1633382622318',
            $responseArray['result']['UF_CRM_1633382622318']));
        $lead->setTypeService($this->getFieldListId(
            'UF_CRM_1654604584522',
            $responseArray['result']['UF_CRM_1654604584522']));
        $lead->setCreditorsAndDebt($responseArray['result']['UF_CRM_1669293769']);
        $lead->setSourcesOfIncome($responseArray['result']['UF_CRM_1669293853']);
        $lead->setFamilySituation($responseArray['result']['UF_CRM_1657769573776']);
        $lead->setProperty($responseArray['result']['UF_CRM_1669293874']);
        $lead->setTransactions($responseArray['result']['UF_CRM_1669287731']);
        $lead->setFalseDateToCreditors($responseArray['result']['UF_CRM_1669287789']);
        $lead->setCriminalLiability($responseArray['result']['UF_CRM_1669287861']);
        $lead->setPreservationOfProperty($responseArray['result']['UF_CRM_1669287897']);
        $lead->setSubjectGeneralAgreement($responseArray['result']['UF_CRM_1669292595']);
        $lead->setCreditInstitution($responseArray['result']['UF_CRM_1657784480387']);
        $lead->setCityOfRefinancing($responseArray['result']['UF_CRM_CITY']);

        return $lead;
    }

    public function setLead(Lead $lead, $classBefore, $sqlLead)
    {
        $safeMySQL = new SafeMySQL;

        $methodQuery = QueryHelper::getQuery($classBefore, 'crm.lead.add', [
            'fields' => [
                'NAME' => $lead->getName(), // Имя
                'LAST_NAME' => $lead->getLastName(), // Фамилия
                'SECOND_NAME' => $lead->getSecondName(), // Отчество
                'PHONE' => [[
                    'VALUE' => $lead->getPhone(),
                    'VALUE_TYPE' => $lead->getTypePhone()
                ]], // Телефон
                'CONTACT_ID' => $lead->getContactId(), //контакт
                'UF_CRM_1647594109' => $lead->getOperator(), //оператор кц
                'UF_CRM_1650282793' => $lead->getDivisions(), //Подразделения
                'COMMENTS' => $lead->getComments(), //Комментарий
                'UF_CRM_1654253931573' => $lead->getCity(), // Город
                'UF_CRM_1565700259' => $lead->getAgency(), //Город представительство
                'SOURCE_ID' => $lead->getSourceId(), // Источник
                'STATUS_ID' => $lead->getStatusId(), // Статус
                'ASSIGNED_BY_ID' => $lead->getAssignedById(), //Ответственный
                'UF_CRM_1620044630' => $lead->getDateMeeting(), //Дата и время встречи
//                'OBSERVER', //Наблюдатели
                'UF_CRM_1571200098221' => $lead->getService(), //Услуга
                'UF_CRM_1633382622318' => $lead->getRefusals(), //Отказы КЦ
                'UF_CRM_1654604584522' => $lead->getTypeService(), //Тип услуги
                'UF_CRM_1669293769' => $lead->getcreditorsAndDebt(), //Кредиторы и суммы долга
                'UF_CRM_1669293853' => $lead->getSourcesOfIncome(), //Источники дохода
                'UF_CRM_1657769573776' => $lead->getFamilySituation(),  //Семейной положение
                'UF_CRM_1669293874' => $lead->getProperty(), //Имущество
                'UF_CRM_1669287731' => $lead->getTransactions(), //Сделки за последние 3 года
                'UF_CRM_1669287789' => $lead->getFalseDateToCreditors(), //Предоставление недостоверных данных кредиторам
                'UF_CRM_1669287861' => $lead->getCriminalLiability(), //Уголовная ответственность
                'UF_CRM_1669287897' => $lead->getPreservationOfProperty(), //Сохранение имущества
                'UF_CRM_1669292595' => $lead->getSubjectGeneralAgreement(), //Предмет Общего договора
                'UF_CRM_1657784480387' => $lead->getCreditInstitution(), //Кредитная организация или лицо, выдавшие кредит
                'UF_CRM_CITY' => $lead->getCityOfRefinancing(), //Город рефенансирование
                'UF_CRM_1720601659' => $lead->getId(), //Облачный ID
            ]
        ]);

        $safeMySQL->query($sqlLead, $methodQuery['result'], $lead->getId());
    }

    public function updateLead(Lead $lead, $classBefore)
    {
        $methodQuery = QueryHelper::getQuery($classBefore, 'crm.lead.update', [
            'id' => $lead->getId(),
            'fields' => [
                'NAME' => $lead->getName(), // Имя
                'LAST_NAME' => $lead->getLastName(), // Фамилия
                'SECOND_NAME' => $lead->getSecondName(), // Отчество
                'PHONE' => [[
                    'VALUE' => $lead->getPhone(),
                    'VALUE_TYPE' => $lead->getTypePhone()
                ]], // Телефон
                'CONTACT_ID' => $lead->getContactId(), //контакт
                'UF_CRM_1647594109' => $lead->getOperator(), //оператор кц
                'UF_CRM_1650282793' => $lead->getDivisions(), //Подразделения
                'COMMENTS' => $lead->getComments(), //Комментарий
                'UF_CRM_1654253931573' => $lead->getCity(), // Город
                'UF_CRM_1565700259' => $lead->getAgency(), //Город представительство
                'SOURCE_ID' => $lead->getSourceId(), // Источник
                'STATUS_ID' => $lead->getStatusId(), // Статус
                'ASSIGNED_BY_ID' => $lead->getAssignedById(), //Ответственный
                'UF_CRM_1620044630' => $lead->getDateMeeting(), //Дата и время встречи
//                'OBSERVER', //Наблюдатели
                'UF_CRM_1571200098221' => $lead->getService(), //Услуга
                'UF_CRM_1633382622318' => $lead->getRefusals(), //Отказы КЦ
                'UF_CRM_1654604584522' => $lead->getTypeService(), //Тип услуги
                'UF_CRM_1669293769' => $lead->getcreditorsAndDebt(), //Кредиторы и суммы долга
                'UF_CRM_1669293853' => $lead->getSourcesOfIncome(), //Источники дохода
                'UF_CRM_1657769573776' => $lead->getFamilySituation(),  //Семейной положение
                'UF_CRM_1669293874' => $lead->getProperty(), //Имущество
                'UF_CRM_1669287731' => $lead->getTransactions(), //Сделки за последние 3 года
                'UF_CRM_1669287789' => $lead->getFalseDateToCreditors(), //Предоставление недостоверных данных кредиторам
                'UF_CRM_1669287861' => $lead->getCriminalLiability(), //Уголовная ответственность
                'UF_CRM_1669287897' => $lead->getPreservationOfProperty(), //Сохранение имущества
                'UF_CRM_1669292595' => $lead->getSubjectGeneralAgreement(), //Предмет Общего договора
                'UF_CRM_1657784480387' => $lead->getCreditInstitution(), //Кредитная организация или лицо, выдавшие кредит
                'UF_CRM_CITY' => $lead->getCityOfRefinancing(), //Город рефенансирование
                'UF_CRM_1720601659' => $lead->getId(), //Облачный ID
            ]
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
