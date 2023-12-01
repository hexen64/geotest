<?php

namespace App\Entity;

use App\Repository\GroupsRowsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupsRowsRepository::class)]
#[ORM\Table(name: "groups_rows")]
class GroupsRows
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    private string $groupId;

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 32)]
    private string $rowId;

    #[ORM\Column(type: "integer")]
    private int $position;

    // Getters and setters for the fields

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(string $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function getRowId(): ?string
    {
        return $this->rowId;
    }

    public function setRowId(string $rowId): self
    {
        $this->rowId = $rowId;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }
}
