<?php

namespace App\Entity;

use App\Repository\ComplectsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComplectsRepository::class)]
#[ORM\Table(name: '`complects`')]
class Complects
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    private string $id;

    #[ORM\Column(type: "string", length: 250)]
    private string $name;

    #[ORM\Column(type: "string", length: 250)]
    private string $img;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string", length: 250)]
    private string $tag;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $tag_end;

    #[ORM\Column(type: "boolean", options: ["default" => true])]
    private bool $visible;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $file;

    // Getters and setters for the fields

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
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

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(string $img): self
    {
        $this->img = $img;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    public function getTagEnd(): ?\DateTimeInterface
    {
        return $this->tag_end;
    }

    public function setTagEnd(\DateTimeInterface $tag_end): self
    {
        $this->tag_end = $tag_end;
        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }
}
