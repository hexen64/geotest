<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
#[ORM\Table(name: '`orders`')]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: "text")]
    private ?string $fio;

    #[ORM\Column(type: "string", length: 250)]
    private ?string $phone;

    #[ORM\Column(type: "string", length: 250)]
    private ?string $email = null;

    #[ORM\Column(type: "text")]
    private ?string $firm;

    #[ORM\Column(type: "text")]
    private ?string $address;

    #[ORM\Column(type: "text")]
    private ?string $comment;

    #[ORM\Column(type: "string", columnDefinition: "ENUM('firm','firm+sklad','self')", length: 10, options: ["default" => "firm"])]
    private ?string $delivery;

    #[ORM\Column(type: "float")]
    private ?float $total = null;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $submit = false;

    #[ORM\ManyToMany(targetEntity: Rows::class, mappedBy: 'orders')]
    #[ORM\JoinTable(name: "orders_rows")]
    #[ORM\JoinColumn(name: "order_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "row_id", referencedColumnName: "id")]
    private Collection $rows;

    #[ORM\ManyToMany(targetEntity: Variants::class, inversedBy: 'orders')]
    #[ORM\JoinTable(name: "orders_variants")]
    #[ORM\JoinColumn(name: "order_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "variant_id", referencedColumnName: "id")]
    private Collection $variants;

    #[ORM\OneToMany(targetEntity: OrdersRows::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    private Collection $ordersRows;

    #[ORM\OneToMany(targetEntity: OrdersVariants::class, mappedBy: 'order', cascade: ['persist'],  orphanRemoval: true)]
    private Collection $ordersVariants;


    public function __construct()
    {
        $this->rows = new ArrayCollection();
        $this->variants = new ArrayCollection();
        $this->ordersRows = new ArrayCollection();
        $this->ordersVariants = new ArrayCollection();

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

    public function getFio(): ?string
    {
        return $this->fio;
    }

    public function setFio(string $fio): self
    {
        $this->fio = $fio;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getFirm(): ?string
    {
        return $this->firm;
    }

    public function setFirm(string $firm): self
    {
        $this->firm = $firm;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getDelivery(): ?string
    {
        return $this->delivery;
    }

    public function setDelivery(string $delivery): self
    {
        $this->delivery = $delivery;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;
        return $this;
    }

    public function isSubmit(): bool
    {
        return $this->submit;
    }

    public function setSubmit(bool $submit): self
    {
        $this->submit = $submit;
        return $this;
    }

    public function getRows(): Collection
    {
        return $this->rows;

    }

    public function addRow(Rows $row): self
    {
        if (!$this->rows->contains($row)) {
            $this->rows[] = $row;
            $row->addOrder($this);
        }
        return $this;
    }

    public function removeRow(Rows $row): self
    {
        if ($this->rows->removeElement($row)) {
            $row->removeOrder($this);
        }
        return $this;

    }

    public function getVariants(): Collection
    {
        return $this->variants;
    }


    public function addVariant(Variants $variant): self
    {
        if (!$this->variants->contains($variant)) {
            $this->variants[] = $variant;
            $variant->addOrder($this);
        }
        return $this;
    }


    public function removeVariant(Variants $variant): self
    {
        if ($this->variants->removeElement($variant)) {
            $variant->removeOrder($this);
        }
        return $this;
    }

    public function getOrdersRows(): Collection
    {
        return $this->ordersRows;

    }

    public function addOrdersRows(OrdersRows $ordersRows): self
    {
        if (!$this->ordersRows->contains($ordersRows)) {
            $this->ordersRows[] = $ordersRows;
            $ordersRows->setOrder($this);
        }
        return $this;
    }

    public function removeOrdersRows(OrdersRows $ordersRows): self
    {
        if ($this->ordersRows->removeElement($ordersRows)) {
            if ($ordersRows->getOrder() === $this) {
                $ordersRows->setOrder(null);
            }
        }
        return $this;
    }

    public function getOrdersVariants(): Collection
    {
        return $this->ordersVariants;


    }

    public function addOrdersVariants(OrdersVariants $ordersVariants): self
    {
        if (!$this->ordersVariants->contains($ordersVariants)) {
            $this->ordersVariants[] = $ordersVariants;
            $ordersVariants->setOrder($this);
        }
        return $this;
    }

    public function removeOrdersVariants(OrdersVariants $ordersVariants): self
    {
        if ($this->ordersVariants->removeElement($ordersVariants)) {
            if ($ordersVariants->getOrder() === $this) {
                $ordersVariants->setOrder(null);
            }
        }
        return $this;

    }

    public function toArray() : array
    {
        return [
            'id' => $this->getId(),
            'fio' => $this->getFio(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
            'firm' => $this->getFirm(),
            'address' => $this->getAddress(),
            'comment' => $this->getComment(),
            'delivery' => $this->getDelivery(),
            'total' => $this->getTotal(),
            'submit' => $this->isSubmit(),
            'variants' => $this->getVariants()->toArray(),
            'rows' => $this->getRows()->toArray(),
            'orderVariants' => $this->getOrdersVariants(),
            'orderRows' => $this->getOrdersRows(),
        ];
    }
}