<?php

namespace App\Model;

use App\Service\ContactService;

class Contact
{
    private ?int $id;
    private ?string $city;
    private ?string $name;
    private ?string $secondName;
    private ?string $lastName;
    private ?string $phoneValueType;
    private $phoneValue;
    private $birthdate;
    private $address;
    private $emailValue;
    private $emailValueType;
    private $passportSerial;
    private $passportNumber;
    private $dateIssue;
    private $departmentCode;
    private $issuer;
    private $leadId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    public function setSecondName(?string $secondName): void
    {
        $this->secondName = $secondName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getPhoneValueType(): ?string
    {
        return $this->phoneValueType;
    }

    public function setPhoneValueType(?string $phoneValueType): void
    {
        $this->phoneValueType = $phoneValueType;
    }

    public function getPhoneValue()
    {
        return $this->phoneValue;
    }

    public function setPhoneValue($phoneValue): void
    {
        $this->phoneValue = $phoneValue;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address): void
    {
        $this->address = $address;
    }

    public function getEmailValue()
    {
        return $this->emailValue;
    }

    public function setEmailValue($emailValue): void
    {
        $this->emailValue = $emailValue;
    }

    public function getEmailValueType()
    {
        return $this->emailValueType;
    }

    public function setEmailValueType($emailValueType): void
    {
        $this->emailValueType = $emailValueType;
    }

    public function getPassportSerial()
    {
        return $this->passportSerial;
    }

    public function setPassportSerial($passportSerial): void
    {
        $this->passportSerial = $passportSerial;
    }

    public function getPassportNumber()
    {
        return $this->passportNumber;
    }

    public function setPassportNumber($passportNumber): void
    {
        $this->passportNumber = $passportNumber;
    }

    public function getDateIssue()
    {
        return $this->dateIssue;
    }

    public function setDateIssue($dateIssue): void
    {
        $this->dateIssue = $dateIssue;
    }

    public function getDepartmentCode()
    {
        return $this->departmentCode;
    }

    public function setDepartmentCode($departmentCode): void
    {
        $this->departmentCode = $departmentCode;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function setIssuer($issuer): void
    {
        $this->issuer = $issuer;
    }

    public function getLeadId()
    {
        return $this->leadId;
    }

    public function setLeadId($leadId): void
    {
        $this->leadId = $leadId;
    }

    public function saveContact($classFrom, $classBefore, $contactId)
    {
        $safeMySQL = new SafeMySQL;
        $contactService = new ContactService;

        //Вывод контакта
        $contact = $contactService->getContact($classFrom, $contactId);

        $sqlBeforeId = $safeMySQL
            ->getRow("SELECT `contact_box` FROM det_contact where contact_cloud = ?i", (int)$contactId);
        if (!empty($contact->getLeadId())) {
            $sqlLead = $safeMySQL
                ->getRow("SELECT `lead_box` FROM det_lead where lead_cloud = ?i", $contact->getLeadId());
        }

        if (empty($sqlBeforeId)) {
            $contactService->setContact();
        } else {
            $contactService->updateContact();
        }
        writeToLog($contact);
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
