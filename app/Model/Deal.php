<?php

namespace App\Model;

class Deal
{

    private string $title;
    private $comments;
    private $summaDebt;
    private int $numberDeal;
    private string $judgeFio;
    private $dateCourt;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
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

    public function getNumberDeal(): int
    {
        return $this->numberDeal;
    }

    public function setNumberDeal(int $numberDeal): void
    {
        $this->numberDeal = $numberDeal;
    }

    public function getJudgeFio(): string
    {
        return $this->judgeFio;
    }

    public function setJudgeFio(string $judgeFio): void
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


}