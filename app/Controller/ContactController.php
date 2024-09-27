<?php

namespace App\Controller;

use App\Model\Contact;
use App\Model\CRestBox;
use App\Model\CRestCloud;

class ContactController
{
    private Contact $contact;

    public function __construct()
    {
        $this->contact = new Contact;
    }

    public function store($contactId) {
        $classFrom = CRestCloud::class;
        $classBefore = CRestBox::class;

        $this->contact->saveContact($classFrom, $classBefore, $contactId);
    }
}
