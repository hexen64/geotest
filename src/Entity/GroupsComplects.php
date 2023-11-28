<?php

namespace App\Entity;

use App\Repository\GroupsComplectsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupsComplectsRepository::class)]
#[ORM\Table(name: "groups_complects")]

class GroupsComplects
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    private string $group_id;

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    private string $complect_id;

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

    public function getComplectId(): ?string
    {
        return $this->complect_id;
    }

    public function setComplectId(string $complect_id): self
    {
        $this->complect_id = $complect_id;
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
