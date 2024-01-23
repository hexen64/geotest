<?php

namespace App\Entity;

use App\Repository\OrdersRowsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRowsRepository::class)]
#[ORM\Table(name: '`orders_rows`')]
class OrdersRows
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: "order_id")]
    private $orderId;

    #[ORM\Column(type: "string", length: 32, name: "row_id")]
    private $rowId;

    #[ORM\Column(type: "integer")]
    private $cnt;

    #[ORM\Column(type: "string", columnDefinition: "ENUM('k','l')", length: 1)]
    private ?string $type;

    #[ORM\ManyToOne(targetEntity: Rows::class, inversedBy: 'ordersRows', cascade: ['persist'])]
    private $row;

    #[ORM\ManyToOne(targetEntity: Orders::class, inversedBy: 'ordersRows', cascade: ['persist'])]
    private $order;

    private string $name;

    private ?float $price;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    public function getOrder(): ?Orders
    {
        return $this->order;
    }

    public function setOrder(?Orders $order): self
    {
        $this->order = $order;

        return $this;
    }

    #[ORM\PrePersist]
    public function setName(): self
    {
        $this->name = $this->getRow()->getName();
        return $this;
    }

    public function getName(): string
    {
        return $this->getRow()->getName();
    }

    #[ORM\PrePersist]
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getPrice(): float|null
    {
        return $this->price;
    }

}
