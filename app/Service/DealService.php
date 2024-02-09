<?php

namespace App\Service;

use App\Model\Contact;
use App\Model\Deal;

class DealService
{

    public function getDealContact($dealId) : Contact
    {
        $responseArray = getQueryBatch('CRestTula', [
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

        if($responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] == 53){
            $responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] = 'Тула';
        } elseif ($responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] == 55){
            $responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] = 'Владимир';
        } else {
            $responseArray['result']['result']['deal']['UF_CRM_62D05D7F42F09'] = 'Другой (ОНЛАЙН)';
        }

        return $this->buildDealFromResponseArray($responseArray);
    }

    public function buildDealFromResponseArray($responseArray)
    {
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
        $contact->setPassportSerial(mb_substr($responseArray['result']['result']['contact']['UF_CRM_6333543A1D22F'], 0, 4));
        $contact->setPassportNumber(mb_substr($responseArray['result']['result']['contact']['UF_CRM_6333543A1D22F'], -6, 6));

        $deal = new Deal;
        $deal->setTitle($responseArray['result']['result']['deal']['TITLE']);
        $deal->setComments($responseArray['result']['result']['deal']['COMMENTS']);
        $deal->setSummaDebt($responseArray['result']['result']['deal']['UF_CRM_6333543AAB9A1']);
        $deal->setNumberDeal($responseArray['result']['result']['deal']['UF_CRM_1664374736018']);
        $deal->setJudgeFio($responseArray['result']['result']['deal']['UF_CRM_1664373248467']);
        $deal->setDateCourt($responseArray['result']['result']['deal']['UF_CRM_1664374644067']);
    }

    public function updateDeal($sqlDealId, $stage)
    {
        return getQuery('CRestUfa', 'crm.deal.update',[
            'ID' => $sqlDealId,
            'fields' => [
                'STAGE_ID' => $stage,
            ],]);
    }

}
