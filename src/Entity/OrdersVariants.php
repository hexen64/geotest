<?php

namespace App\Entity;

use App\Repository\OrdersVariantsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersVariantsRepository::class)]
#[ORM\Table(name: '`orders_variants`')]
class OrdersVariants
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "integer", name: "order_id")]
    private int $orderId;

    #[ORM\Column(type: "string", length: 50, name: "variant_id")]
    private string $variantId;

    #[ORM\Column(type: "boolean", name: "is_base")]
    private bool $isBase;

    #[ORM\Column(type: "integer")]
    private int $cnt;

    #[ORM\Column(type: "string", length: 1, columnDefinition: "ENUM('k','l')", options: ["default" => "k"])]
    private ?string $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getVariantId(): ?string
    {
        return $this->variantId;
    }

    public function setVariantId(string $variantId): self
    {
        $this->variantId = $variantId;

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
}
