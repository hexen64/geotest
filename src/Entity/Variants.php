<?php

namespace App\Entity;

use App\Repository\VariantsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VariantsRepository::class)]
#[ORM\Table(name: '`variants`')]
class Variants
{

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    private $id;

    #[ORM\Column(type: "string", length: 50, name: "complect_id")]
    private $complectId;

    #[ORM\Column(type: "string", length: 250)]
    private $name;

    #[ORM\Column(type: "float", name: "price_base")]
    private $priceBase;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $isBase;

    #[ORM\Column(type: "integer",  options: ["default" => 0])]
    private $position;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    private $visible;

    #[ORM\Column(type: "text")]
    private $description;

    #[ORM\ManyToMany(targetEntity: Rows::class, mappedBy: "variants")]
    #[ORM\JoinTable(name: "variants_rows")]
    #[ORM\JoinColumn(name: "variant_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "row_id", referencedColumnName: "id")]
    private Collection $rows;

    #[ORM\ManyToMany(targetEntity: Orders::class, mappedBy: 'variants')]
    #[ORM\JoinTable(name: "orders_variants")]
    #[ORM\JoinColumn(name: "variant_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "order_id", referencedColumnName: "id")]
    private Collection $orders;


    #[ORM\OneToMany(targetEntity: VariantsRows::class, mappedBy: "variant", cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $variantsRows;

    #[ORM\OneToMany(targetEntity: OrdersVariants::class, mappedBy: "variant", orphanRemoval: true)]
    private Collection $ordersVariants;

    private ?string $type;
    private string $price;

    #[Assert\Type(
        type: 'integer',
        message: 'Некорректное значение.',
    )]
    private int $cnt;


    public function __construct()
    {
        $this->rows = new ArrayCollection();
        $this->variantsRows = new ArrayCollection();
        $this->ordersVariants = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getComplectId(): ?string
    {
        return $this->complectId;
    }

    public function setComplectId(string $complectId): self
    {
        $this->complectId = $complectId;

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

    public function getPriceBase(): ?float
    {
        return $this->priceBase;
    }

    public function setPriceBase(float $priceBase): self
    {
        $this->priceBase = $priceBase;

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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
            $row->addVariant($this);
        }

        return $this;
    }

    public function removeRow(Rows $row): self
    {
        if ($this->rows->removeElement($row)) {
            $row->removeVariant($this);
        }

        return $this;
    }

    public function getVariantsRows(): Collection
    {
        return $this->variantsRows;
    }

    public function addVariantsRow(VariantsRows $variantsRow): self
    {
        if (!$this->variantsRows->contains($variantsRow)) {
            $this->variantsRows[] = $variantsRow;
            $variantsRow->setVariant($this);
        }

        return $this;
    }

    public function removeVariantsRow(VariantsRows $variantsRow): self

    {
        if ($this->variantsRows->removeElement($variantsRow)) {
            if ($variantsRow->getVariant() === $this) {
                $variantsRow->setVariant(null);
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
            $ordersVariants->setVariant($this);
        }

        return $this;
    }


    public function removeOrdersVariants(OrdersVariants $ordersVariants): self
    {
        if ($this->ordersVariants->removeElement($ordersVariants)) {
            // set the owning side to null (unless already changed)
            if ($ordersVariants->getVariant() === $this) {
                $ordersVariants->setVariant(null);
            }
        }

        return $this;
    }

    public function getOrders(): Collection
    {
        return $this->orders;

    }

    public function addOrder(Orders $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->addVariant($this);
        }

        return $this;

    }

    public function removeOrder(Orders $order): self
    {
        if ($this->orders->removeElement($order)) {
            $order->removeVariant($this);
        }

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

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getCnt(): int
    {
        return $this->cnt;
    }

    /**
     * @param int $cnt
     */
    public function setCnt(int $cnt): void
    {
        $this->cnt = $cnt;
    }


}
