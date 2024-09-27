<?php

namespace App\Model;

use App\Service\ContactService;

class Contact
{
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
    private $IssuerWithIssueAt;
    private $issueAt;
    private $issuer;

    public function saveContact($classFrom, $classBefore, $contactId)
    {
        $safeMySQL = new SafeMySQL;
        $contactService = new ContactService;

        //Вывод контакта
        $contact = $contactService->getContact($classFrom, $contactId);

        $sqlBeforeId = $safeMySQL
            ->getRow("SELECT `contact_box` FROM det_contact where contact_cloud = ?i", (int)$contactId);


    }

}