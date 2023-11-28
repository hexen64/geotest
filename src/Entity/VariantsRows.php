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
    private $order;

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

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;

        return $this;
    }
}
