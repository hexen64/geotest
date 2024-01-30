<?php

namespace App\Entity;

use App\Repository\VariantsRowsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VariantsRowsRepository::class)]
#[ORM\Table(name: '`variants_rows`')]
class VariantsRows
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50, name: "variant_id")]
    private string $variantId;

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 32, name: "row_id")]
    private string $rowId;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    #[Assert\Type(
        type: 'integer',
        message: 'Некорректное значение.',
    )]
    private int $cnt;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private bool $isBase;

    #[ORM\Column(type: "smallint", options: ["default" => 0])]
    private int $position;

    #[ORM\ManyToOne(targetEntity: Rows::class, inversedBy: 'variantsRows', cascade: ["persist"])]
    private Rows $row;

    #[ORM\ManyToOne(targetEntity: Variants::class, inversedBy: 'variantsRows', cascade: ["persist"])]
    private Variants $variant;

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
