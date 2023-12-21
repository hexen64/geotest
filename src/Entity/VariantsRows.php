<?php

namespace App\Entity;

use App\Repository\VariantsRowsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VariantsRowsRepository::class)]
#[ORM\Table(name: '`variants_rows`')]
class VariantsRows
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50, name: "variant_id")]
    private $variantId;

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 32, name: "row_id")]
    private $rowId;

    #[ORM\Column(type: "integer", options: ["default" => 1])]
    private $cnt;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $isBase;

    #[ORM\Column(type: "smallint", options: ["default" => 0])]
    private $position;


    #[ORM\ManyToOne(targetEntity: Rows::class, inversedBy: 'variantsRows')]
    private Rows $row;

    #[ORM\ManyToOne(targetEntity: Variants::class, inversedBy: 'variantsRows')]
    private ?Variants $variant;

    public function getVariantId(): ?string
    {
        return $this->variantId;
    }

    public function setVariantId(string $variantId): self
    {
        $this->variantId = $variantId;

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

    public function getCnt(): ?int
    {
        return $this->cnt;
    }

    public function setCnt(int $cnt): self
    {
        $this->cnt = $cnt;

        return $this;
    }

    public function getIsBase(): ?bool
    {
        return $this->isBase;
    }

    public function setIsBase(bool $isBase): self
    {
        $this->isBase = $isBase;

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


    public function getRow(): ?Rows
    {
        return $this->row;

    }


    public function setRow(?Rows $row): self
    {
        $this->row = $row;

        return $this;

    }

    public function getVariant(): ?Variants
    {
        return $this->variant;

    }


    public function setVariant(?Variants $variant): self
    {
        $this->variant = $variant;

        return $this;

    }
}
