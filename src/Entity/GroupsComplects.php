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
    private string $groupId;

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    private string $complectId;

    #[ORM\Column(type: "integer")]
    private int $position;


    #[ORM\ManyToOne(targetEntity: Groups::class, inversedBy: "groupsComplects")]
    private Groups $group;


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

    public function getComplectId(): ?string
    {
        return $this->complectId;
    }

    public function setComplectId(string $complectId): self
    {
        $this->complectId = $complectId;
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

    public function getGroup(): ?Groups
    {
        return $this->group;

    }

    public function setGroup(?Groups $group): self
    {
        $this->group = $group;
        return $this;
    }
}
