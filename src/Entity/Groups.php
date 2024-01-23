<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`groups`')]
class Groups
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    #[ORM\GeneratedValue]
    private string $id;

    #[ORM\Column(type: "string", length: 250)]
    private string $name;

    #[ORM\Column(type: "string", length: 250)]
    private string $fullname;

    #[ORM\Column(type: "smallint")]
    private int $position;

    #[ORM\Column(type: "smallint", options: ["default" => 1])]
    private int $visible;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $cnt;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $file;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $newsId;

    // Getters and setters for the fields

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }


    public function getFullname(): ?string
    {
        return $this->fullname;

    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;
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

    public function getVisible(): ?int
    {
        return $this->visible;
    }


    public function setVisible(int $visible): self
    {
        $this->visible = $visible;
        return $this;
    }


    public function getCnt(): ?int
    {
        return $this->cnt;
    }


    public function setCnt(int $cnt): self
    {
        $this->cnt = $cnt;
        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getNewsId(): ?int
    {
        return $this->newsId;
    }

    public function setNewsId(?int $newsId): self
    {
        $this->newsId = $newsId;
        return $this;
    }

}



