<?php

namespace App\Entity;

use App\Repository\RowsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RowsRepository::class)]
#[ORM\Table(name: '`rows_t`')]
class Rows
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 32)]
    private $id;

    #[ORM\Column(type: "string", length: 50, name: "idk")]
    private $idk;

    #[ORM\Column(type: "string", length: 50, name: "idl")]
    private $idl;

    #[ORM\Column(type: "string", length: 250)]
    private $name;

    #[ORM\Column(type: "float")]
    private $price;

    #[ORM\Column(type: "string", length: 50)]
    private $file;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $fixed;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    private $visible;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getIdk(): ?string
    {
        return $this->idk;
    }

    public function setIdk(string $idk): self
    {
        $this->idk = $idk;

        return $this;
    }

    public function getIdl(): ?string
    {
        return $this->idl;
    }

    public function setIdl(string $idl): self
    {
        $this->idl = $idl;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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

    public function getFixed(): ?bool
    {
        return $this->fixed;
    }

    public function setFixed(bool $fixed): self
    {
        $this->fixed = $fixed;

        return $this;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }
}
