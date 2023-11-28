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
    private string $group_id;

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 32)]
    private string $row_id;

    #[ORM\Column(type: "integer")]
    private int $position;

    // Getters and setters for the fields

    public function getGroupId(): ?string
    {
        return $this->group_id;
    }

    public function setGroupId(string $group_id): self
    {
        $this->group_id = $group_id;
        return $this;
    }

    public function getRowId(): ?string
    {
        return $this->row_id;
    }

    public function setRowId(string $row_id): self
    {
        $this->row_id = $row_id;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setposition(int $position): self
    {
        $this->position = $position;
        return $this;
    }
}
