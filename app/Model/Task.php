<?php

namespace App\Model;

class Task
{
    private int $id;
    private string $title;
    private string $description;

    private int $dealId;
    private $deadline;
    private $startDatePlan;
    private  int $responsibleId;
    private int $changedBy;
    private $status;
    private $allowChangeDeadline;
    private array $ufTaskWebdavFiles;

    public function getUfTaskWebdavFiles(): array
    {
        return $this->ufTaskWebdavFiles;
    }

    public function setUfTaskWebdavFiles($ufTaskWebdavFiles): void
    {
        $this->ufTaskWebdavFiles = $ufTaskWebdavFiles;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDeadline()
    {
        return $this->deadline;
    }

    public function setDeadline($deadline): void
    {
        $this->deadline = $deadline;
    }

    public function getStartDatePlan()
    {
        return $this->startDatePlan;
    }

    public function setStartDatePlan($startDatePlan): void
    {
        $this->startDatePlan = $startDatePlan;
    }

    public function getChangedBy(): int
    {
        return $this->changedBy;
    }

    public function setChangedBy(int $changedBy): void
    {
        $this->changedBy = $changedBy;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getAllowChangeDeadline()
    {
        return $this->allowChangeDeadline;
    }

    public function setAllowChangeDeadline($allowChangeDeadline): void
    {
        $this->allowChangeDeadline = $allowChangeDeadline;
    }

    public function getResponsibleId(): int
    {
        return $this->responsibleId;
    }

    public function setResponsibleId(int $responsibleId): void
    {
        $this->responsibleId = $responsibleId;
    }

    public function getDealId(): int
    {
        return $this->dealId;
    }

    public function setDealId(string $dealId): void
    {
        $this->dealId = trim($dealId, 'D_');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
