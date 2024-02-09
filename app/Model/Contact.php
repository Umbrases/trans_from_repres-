<?php

namespace App\Model;

class Contact
{

    private string $city;
    private string $name;
    private string $secondName;
    private string $lastName;
    private string $phoneValueType;
    private $phoneValue;
    private $birthdate;
    private $address;
    private $emailValue;
    private $emailValueType;
    private $passportSerial;
    private $passportNumber;

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

    public function getPhoneValue()
    {
        return $this->phoneValue;
    }

    public function setPhoneValue($phoneValue): void
    {
        $this->phoneValue = $phoneValue;
    }

    public function getPhoneValueType(): string
    {
        return $this->phoneValueType;
    }
    public function setPhoneValueType(string $phoneValueType): void
    {
        $this->phoneValueType = $phoneValueType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSecondName(): string
    {
        return $this->secondName;
    }

    public function setSecondName(string $secondName): void
    {
        $this->secondName = $secondName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }


    public function getCity()
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

}