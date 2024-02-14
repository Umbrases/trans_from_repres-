<?php

namespace App\Model;

class Task
{
    private ?int $id;
    private ?string $title;
    private ?int $dealId;

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
