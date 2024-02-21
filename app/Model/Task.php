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

    public function getDealId(): ?int
    {
        return $this->dealId;
    }

    public function setDealId(?string $dealId = null): void
    {
        $this->dealId = trim($dealId, 'D_');
    }

    /**
     * @return mixed
     */
    public function getTaskFile()
    {
        return $this->taskFile;
    }

    /**
     * @param mixed $taskFile
     */
    public function setTaskFile($taskFile): void
    {
        $this->taskFile = $taskFile;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * @param mixed $deadline
     */
    public function setDeadline($deadline): void
    {
        $this->deadline = $deadline;
    }

    /**
     * @return mixed
     */
    public function getStartDatePlan()
    {
        return $this->startDatePlan;
    }

    /**
     * @param mixed $startDatePlan
     */
    public function setStartDatePlan($startDatePlan): void
    {
        $this->startDatePlan = $startDatePlan;
    }

    public function getChangedBy(): ?int
    {
        return $this->changedBy;
    }

    public function setChangedBy(?int $changedBy = null): void
    {
        $this->changedBy = $changedBy;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getAllowChangeDeadline()
    {
        return $this->allowChangeDeadline;
    }

    /**
     * @param mixed $allowChangeDeadline
     */
    public function setAllowChangeDeadline($allowChangeDeadline): void
    {
        $this->allowChangeDeadline = $allowChangeDeadline;
    }

    public function getResponsibleId(): ?int
    {
        return $this->responsibleId;
    }

    public function setResponsibleId(?int $responsibleId = null): void
    {
        $this->responsibleId = $responsibleId;
    }


}
