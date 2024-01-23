<?php

namespace App\Entity;

use App\Repository\OrdersVariantsRowsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersVariantsRowsRepository::class)]
#[ORM\Table(name: '`orders_variants_rows`')]
class OrdersVariantsRows
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: "order_variant_id")]
    private $orderVariantId;

    #[ORM\Column(type: "string", length: 32, name: "row_id")]
    private $rowId;

    #[ORM\Column(type: "integer")]
    private $diff;

    private string $name;

    #[ORM\ManyToOne(targetEntity: OrdersVariants::class, inversedBy: 'ordersVariantsRows', cascade: ['persist'])]
    private OrdersVariants $orderVariant;


    #[ORM\ManyToOne(targetEntity: Rows::class, inversedBy: 'ordersVariantsRows', cascade: ['persist'])]
    private Rows $row;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getOrderVariantId(): ?int
    {
        return $this->orderVariantId;
    }

    public function setOrderVariantId(int $orderVariantId): self
    {
        $this->orderVariantId = $orderVariantId;

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

    public function getDiff(): ?int
    {
        return $this->diff;
    }

    public function setDiff(int $diff): self
    {
        $this->diff = $diff;

        return $this;
    }

    public function getOrderVariant(): ?OrdersVariants
    {
        return $this->orderVariant;
    }

    public function setOrderVariant(?OrdersVariants $orderVariant): self
    {
        $this->orderVariant = $orderVariant;

        return $this;
    }

    #[ORM\PrePersist]
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->getRow()->getName();
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

}
