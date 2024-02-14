<?php

namespace App\Model;

class Deal
{

    private ?string $title;
    private $comments;
    private $summaDebt;
    private ?int $numberDeal;
    private ?string $judgeFio;
    private $dateCourt;
    private Contact $contact;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title = null): void
    {
        $this->title = $title;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments): void
    {
        $this->comments = $comments;
    }

    public function getSummaDebt()
    {
        return $this->summaDebt;
    }

    public function setSummaDebt($summaDebt): void
    {
        $this->summaDebt = $summaDebt;
    }

    public function getNumberDeal(): ?int
    {
        return $this->numberDeal;
    }

    public function setNumberDeal(?int $numberDeal = null): void
    {
        $this->numberDeal = $numberDeal;
    }

    public function getJudgeFio(): ?string
    {
        return $this->judgeFio;
    }

    public function setJudgeFio(?string $judgeFio= null): void
    {
        $this->judgeFio = $judgeFio;
    }

    public function getDateCourt()
    {
        return $this->dateCourt;
    }

    public function setDateCourt($dateCourt): void
    {
        $this->dateCourt = $dateCourt;
    }

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): void
    {
        $this->contact = $contact;
    }
}
