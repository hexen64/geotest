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

    #[ORM\ManyToOne(targetEntity: "App\Entity\Orders")]
    #[ORM\JoinColumn(name: "order_id", referencedColumnName: "id")]
    private $order;

    #[ORM\Column(type: "string", length: 32, name: "row_id")]
    private $rowId;

    #[ORM\Column(type: "integer")]
    private $cnt;

    #[ORM\Column(type: "string", columnDefinition: "ENUM('k','l')", length: 1, options: ["default" => "k"])]
    private $type;

    public function getId(): ?int
    {
        return $this->id;
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

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
