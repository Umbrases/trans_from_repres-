<?php

namespace App\Model;

class Task
{
    private ?int $id;
    private ?string $title;
    private ?int $dealId;
    private $taskFile;
    private $description;
    private $deadline;
    private $startDatePlan;
    private ?int $changedBy;
    private $status;
    private $allowChangeDeadline;
    private ?int $responsibleId;

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description): void
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

    public function getChangedBy()
    {
        return $this->changedBy;
    }

    public function setChangedBy($changedBy): void
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

    public function getTaskFile()
    {
        return $this->taskFile;
    }

    public function setTaskFile($taskFile): void
    {
        $this->taskFile = $taskFile;
    }

    private ?int $responsibleId;

    public function getResponsibleId(): ?int
    {
        return $this->responsibleId;
    }

    public function setResponsibleId(?int $responsibleId = null): void
    {
        $this->responsibleId = $responsibleId;
    }

    public function getDealId(): ?int
    {
        return $this->dealId;
    }

    public function setDealId(?string $dealId = null): void
    {
        $this->dealId = trim($dealId, 'D_');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id = null): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title = null): void
    {
        $this->title = $title;
    }
}
