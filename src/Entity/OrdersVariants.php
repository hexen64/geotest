<?php

namespace App\Entity;

use App\Repository\OrdersVariantsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type: "string", length: 1, columnDefinition: "ENUM('k','l')", options: ["default" => "l"])]
    private ?string $type;

    #[ORM\ManyToOne(targetEntity: Variants::class, inversedBy: 'ordersVariants', cascade: ['persist'])]
    private Variants $variant;

    #[ORM\ManyToOne(targetEntity: Orders::class, inversedBy: 'ordersVariants', cascade: ['persist'])]
    private Orders $order;

    #[ORM\OneToMany(mappedBy: 'orderVariant', targetEntity: OrdersVariantsRows::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $ordersVariantsRows;

    private string $name;

    private float $price;

    private int $diff;


    public function __construct()
    {
        $this->ordersVariantsRows = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getVariant(): ?Variants
    {
        return $this->variant;
    }

    public function setVariant(?Variants $variant): self
    {
        $this->variant = $variant;

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

    public function getRows(): Collection
    {
        return $this->ordersVariantsRows;
    }


    public function addRow(OrdersVariantsRows $row): self
    {
        if (!$this->ordersVariantsRows->contains($row)) {
            $this->ordersVariantsRows[] = $row;
            $row->setOrderVariant($this);
        }

        return $this;
    }

    public function removeRow(OrdersVariantsRows $row): self
    {
        if ($this->ordersVariantsRows->removeElement($row)) {
            if ($row->getOrderVariant() === $this) {
                $row->setOrderVariant(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function setName(): self
    {
        $this->name = $this->getVariant()->getName();
        return $this;
    }

    public function getName(): string
    {
        return $this->getVariant()->getName();
    }

    #[ORM\PrePersist]
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }


}
