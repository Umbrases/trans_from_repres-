<?php

namespace App\Service;

use App\Model\Contact;
use App\Model\Deal;
use App\Model\QueryHelper;
use App\Model\SafeMySQL;

class DealService
{

    private QueryHelper $queryHelper;

    private SafeMySQL $safeMySQL;

    public function __construct()
    {
        $this->queryHelper = new QueryHelper;
        $this->safeMySQL = new SafeMySQL;
    }
    public function getDeal($dealId): Deal
    {
        //Запрос информации о сделке и клиенте
        $responseArray = $this->queryHelper->getQueryBatch('CRestTula', [
            'deal' => [
                'method' => 'crm.deal.get',
                'params' => [
                    'ID' => $dealId
                ]
            ],
            'contact' => [
                'method' => 'crm.contact.get',
                'params' => [
                    'ID' => '$result[deal][CONTACT_ID]'
                ]
            ],
        ]);

        //Сравнение id переменной с городом
        if($responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] == 53){
            $responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] = 'Тула';
        } elseif ($responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] == 55){
            $responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] = 'Владимир';
        } else {
            $responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] = 'Другой (ОНЛАЙН)';
        }

        return $this->buildDealFromResponseArray($responseArray);
    }

    public function buildDealFromResponseArray($responseArray): Deal
    {
        $deal = new Deal;
        $deal->setTitle($responseArray['result']['result']['deal']['TITLE']);
        $deal->setComments($responseArray['result']['result']['deal']['COMMENTS']);
        $deal->setSummaDebt($responseArray['result']['result']['deal']['UF_CRM_6333543AAB9A1']);
        $deal->setNumberDeal($responseArray['result']['result']['deal']['UF_CRM_1664374736018']);
        $deal->setJudgeFio($responseArray['result']['result']['deal']['UF_CRM_1664373248467']);
        $deal->setDateCourt($responseArray['result']['result']['deal']['UF_CRM_1664374644067']);

        $contact = new Contact;
        $contact->setCity($responseArray['result']['result']['contact']['UF_CRM_62D05D7F42F09']);
        $contact->setName($responseArray['result']['result']['contact']['NAME']);
        $contact->setLastName($responseArray['result']['result']['contact']['SECOND_NAME']);
        $contact->setSecondName($responseArray['result']['result']['contact']['LAST_NAME']);
        $contact->setPhoneValue($responseArray['result']['result']['contact']['PHONE'][0]['VALUE']);
        $contact->setPhoneValueType($responseArray['result']['result']['contact']['PHONE'][0]['VALUE_TYPE']);
        $contact->setBirthdate($responseArray['result']['result']['contact']['BIRTHDATE']);
        $contact->setAddress($responseArray['result']['result']['contact']['ADDRESS']);
        $contact->setEmailValue($responseArray['result']['result']['contact']['EMAIL'][0]['VALUE']);
        $contact->setEmailValueType($responseArray['result']['result']['contact']['EMAIL'][0]['VALUE_TYPE']);
        $contact->setPassportSerial($responseArray['result']['result']['contact']['UF_CRM_6333543A1D22F']);
        $contact->setPassportNumber($responseArray['result']['result']['contact']['UF_CRM_6333543A1D22F']);
        $contact->setIssuerWithIssueAt($responseArray['result']['result']['contact']['UF_CRM_6333543A28B78']);

        // Находим и устанавливаем эмитента и дату выдачи по-отдельности
        $issuerWithIssueAtArray = explode(' ', $contact->getIssuerWithIssueAt());
        $issuerParts = []; // Массив данных об эмитенте(орган выдачи)
        foreach($issuerWithIssueAtArray as $key => $value) {
            //Вытаскиваем дату из массива
            if (strtotime($value)) {
                $contact->setIssueAt($value);
            } else { //Вытаскиваем эмитента
                $issuerParts[] = $value;
            }
        }
        // Собираем части данных об эмитенте обратно в строку
        $contact->setIssuer(implode(' ', $issuerParts));

        // Сеттим контакт в сделку
        $deal->setContact($contact);

        return $deal;
    }

    public function setDeal(Deal $deal, $dealId)
    {
        $ufaAdd = $this->queryHelper->getQueryBatch('CRestUfa', [
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

    public function updateDeal($sqlDealId, $stage)
    {
        //Обновление информации о сделке
        return $this->queryHelper->getQuery('CRestUfa', 'crm.deal.update',[
            'ID' => $sqlDealId,
            'fields' => [
                'STAGE_ID' => $stage,
            ],]);
    }

}
