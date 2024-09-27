<?php

namespace App\Model;

use App\Service\LeadService;

class Lead
{
    private ?int $id;
    private ?string $name;
    private ?string $lastName;
    private ?string $secondName;
    private ?int $contactId;
    private $phone;
    private $typePhone;
    private $operator;
    private $divisions;
    private $comments;
    private $city;
    private $agency;
    private $sourceId;
    private $statusId;
    private ?int $assignedById;
    private $dateMeeting;
//    private $observer;
    private $service;
    private $refusals;
    private $TypeService;
    private $creditorsAndDebt;
    private $sourcesOfIncome;
    private $familySituation;
    private $property;
    private $transactions;
    private $falseDateToCreditors;
    private $criminalLiability;
    private $preservationOfProperty;
    private $subjectGeneralAgreement;
    private $creditInstitution;
    private $cityOfRefinancing;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    public function setSecondName(?string $secondName): void
    {
        $this->secondName = $secondName;
    }

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    public function setContactId(?int $contactId): void
    {
        $this->contactId = $contactId;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    public function getTypePhone()
    {
        return $this->typePhone;
    }

    public function setTypePhone($typePhone): void
    {
        $this->typePhone = $typePhone;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function setOperator($operator): void
    {
        $this->operator = $operator;
    }

    public function getDivisions()
    {
        return $this->divisions;
    }

    public function setDivisions($divisions): void
    {
        $this->divisions = $divisions;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments): void
    {
        $this->comments = $comments;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city): void
    {
        $this->city = $city;
    }

    public function getAgency()
    {
        return $this->agency;
    }

    public function setAgency($agency): void
    {
        $this->agency = $agency;
    }

    public function getSourceId()
    {
        return $this->sourceId;
    }

    public function setSourceId($sourceId): void
    {
        $this->sourceId = $sourceId;
    }

    public function getStatusId()
    {
        return $this->statusId;
    }

    public function setStatusId($statusId): void
    {
        $this->statusId = $statusId;
    }

    public function getAssignedById(): ?int
    {
        return $this->assignedById;
    }

    public function setAssignedById(?int $assignedById): void
    {
        $this->assignedById = $assignedById;
    }

    public function getDateMeeting()
    {
        return $this->dateMeeting;
    }

    public function setDateMeeting($dateMeeting): void
    {
        $this->dateMeeting = $dateMeeting;
    }

//    public function getObserver()
//    {
//        return $this->observer;
//    }

//    public function setObserver($observer): void
//    {
//        $this->observer = $observer;
//    }

    public function getService()
    {
        return $this->service;
    }

    public function setService($service): void
    {
        $this->service = $service;
    }

    public function getRefusals()
    {
        return $this->refusals;
    }

    public function setRefusals($refusals): void
    {
        $this->refusals = $refusals;
    }

    public function getTypeService()
    {
        return $this->TypeService;
    }

    public function setTypeService($TypeService): void
    {
        $this->TypeService = $TypeService;
    }

    public function getCreditorsAndDebt()
    {
        return $this->creditorsAndDebt;
    }

    public function setCreditorsAndDebt($creditorsAndDebt): void
    {
        $this->creditorsAndDebt = $creditorsAndDebt;
    }

    public function getSourcesOfIncome()
    {
        return $this->sourcesOfIncome;
    }

    public function setSourcesOfIncome($sourcesOfIncome): void
    {
        $this->sourcesOfIncome = $sourcesOfIncome;
    }

    public function getFamilySituation()
    {
        return $this->familySituation;
    }

    public function setFamilySituation($familySituation): void
    {
        $this->familySituation = $familySituation;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($property): void
    {
        $this->property = $property;
    }

    public function getTransactions()
    {
        return $this->transactions;
    }

    public function setTransactions($transactions): void
    {
        $this->transactions = $transactions;
    }

    public function getFalseDateToCreditors()
    {
        return $this->falseDateToCreditors;
    }

    public function setFalseDateToCreditors($falseDateToCreditors): void
    {
        $this->falseDateToCreditors = $falseDateToCreditors;
    }

    public function getCriminalLiability()
    {
        return $this->criminalLiability;
    }

    public function setCriminalLiability($criminalLiability): void
    {
        $this->criminalLiability = $criminalLiability;
    }

    public function getPreservationOfProperty()
    {
        return $this->preservationOfProperty;
    }

    public function setPreservationOfProperty($preservationOfProperty): void
    {
        $this->preservationOfProperty = $preservationOfProperty;
    }

    public function getSubjectGeneralAgreement()
    {
        return $this->subjectGeneralAgreement;
    }

    public function setSubjectGeneralAgreement($subjectGeneralAgreement): void
    {
        $this->subjectGeneralAgreement = $subjectGeneralAgreement;
    }

    public function getCreditInstitution()
    {
        return $this->creditInstitution;
    }

    public function setCreditInstitution($creditInstitution): void
    {
        $this->creditInstitution = $creditInstitution;
    }

    public function getCityOfRefinancing()
    {
        return $this->cityOfRefinancing;
    }

    public function setCityOfRefinancing($cityOfRefinancing): void
    {
        $this->cityOfRefinancing = $cityOfRefinancing;
    }

    public function __construct()
    {
        $this->leadService = new LeadService;
    }

    public function saveLead($classFrom, $classBefore, $leadId): void
    {
        $safeMySQL = new SafeMySQL;

        $lead = $this->leadService->getLead($classFrom, $leadId);

        $sqlBeforeId = $safeMySQL->getRow("SELECT `lead_box` FROM det_lead where lead_cloud = ?i", (int)$leadId);
        $sqlLead = "INSERT INTO det_lead SET lead_box = ?i , lead_cloud = ?i";

        //Если лид в коробке отсутствует, то создаем лид
        if (empty($sqlBeforeId)){
            $this->leadService->setLead($lead, $classBefore, $sqlLead);
        } else {
            // ... иначе обновляем его
            $this->leadService->updateLead($lead, $classBefore);
        }
    }
}
