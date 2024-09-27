<?php

namespace App\Controller;

use App\Model\Contact;

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
