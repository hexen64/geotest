<?php

namespace App\Entity;

use App\Repository\RowsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: RowsRepository::class)]
#[ORM\Table(name: '`rows_t`')]
class Rows
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 32)]
    private $id;

    #[ORM\Column(type: "string", length: 50, name: "idk")]
    private $idk;

    #[ORM\Column(type: "string", length: 50, name: "idl")]
    private $idl;

    #[ORM\Column(type: "string", length: 250)]
    private $name;

    #[ORM\Column(type: "float")]
    private $price;

    #[ORM\Column(type: "string", length: 50)]
    private $file;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $fixed;

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    private $visible;

    #[ORM\ManyToMany(targetEntity: Variants::class, inversedBy: "rows")]
    #[ORM\JoinTable(name: "variants_rows")]
    #[ORM\JoinColumn(name: "row_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "variant_id", referencedColumnName: "id")]
    private Collection $variants;


    #[ORM\ManyToMany(targetEntity: Orders::class, inversedBy: "rows")]
    #[ORM\JoinTable(name: "orders_rows")]
    #[ORM\JoinColumn(name: "row_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "order_id", referencedColumnName: "id")]
    private Collection $orders;

    #[ORM\OneToMany(targetEntity: VariantsRows::class, mappedBy: "row", orphanRemoval: true)]
    private Collection $variantsRows;

    #[ORM\OneToMany(targetEntity: OrdersRows::class, mappedBy: "row", orphanRemoval: true)]
    private Collection $ordersRows;


    #[ORM\OneToMany(mappedBy: 'row', targetEntity: OrdersVariantsRows::class, orphanRemoval: true)]
    private Collection $ordersVariantsRows;

    private int $count;

    private int $diff;

    private ?string $type;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->variantsRows = new ArrayCollection();
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

    public function getIdk(): ?string
    {
        return $this->idk;
    }

    public function setIdk(string $idk): self
    {
        $this->idk = $idk;

        return $this;
    }

    public function getIdl(): ?string
    {
        return $this->idl;
    }

    public function setIdl(string $idl): self
    {
        $this->idl = $idl;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFixed(): ?bool
    {
        return $this->fixed;
    }

    public function setFixed(bool $fixed): self
    {
        $this->fixed = $fixed;

        return $this;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

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
            $variant->addRow($this);
        }

        return $this;
    }

    public function removeVariant(Variants $variant): self
    {
        if ($this->variants->removeElement($variant)) {
            $variant->removeRow($this);
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
            $variantsRow->setRow($this);
        }

        return $this;
    }

    public function removeVariantsRow(VariantsRows $variantsRow): self
    {
        if ($this->variantsRows->removeElement($variantsRow)) {
            // Set the owning side to null (unless already changed)
            if ($variantsRow->getRow() === $this) {
                $variantsRow->setRow(null);
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
            $order->addRow($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): self
    {
        if ($this->orders->removeElement($order)) {
            $order->removeRow($this);
        }

        return $this;
    }

    public function getOrdersRows(): Collection
    {
        return $this->ordersRows;

    }


    public function addOrdersRow(OrdersRows $ordersRow): self
    {
        if (!$this->ordersRows->contains($ordersRow)) {
            $this->ordersRows[] = $ordersRow;
            $ordersRow->setRow($this);
        }

        return $this;
    }

    public function removeOrdersRow(OrdersRows $ordersRow): self
    {
        if ($this->ordersRows->removeElement($ordersRow)) {
            // Set the owning side to null (unless already changed)
            if ($ordersRow->getRow() === $this) {
                $ordersRow->setRow(null);
            }
        }

        return $this;


    }

    public function getOrdersVariantsRows(): Collection
    {
        return $this->ordersVariantsRows;

    }


    public function addOrdersVariantsRow(OrdersVariantsRows $ordersVariantsRow): self
    {
        if (!$this->ordersVariantsRows->contains($ordersVariantsRow)) {
            $this->ordersVariantsRows[] = $ordersVariantsRow;
            $ordersVariantsRow->setRow($this);
        }

        return $this;

    }

    public function removeOrdersVariantsRow(OrdersVariantsRows $ordersVariantsRow): self
    {
        if ($this->ordersVariantsRows->removeElement($ordersVariantsRow)) {
            // Set the owning side to null (unless already changed)
            if ($ordersVariantsRow->getRow() === $this) {
                $ordersVariantsRow->setRow(null);
            }
        }

        return $this;

    }

    public function getCount(): int
    {
        return $this->count;

    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getDiff(): int
    {
        return $this->diff;
    }

    public function setDiff(int $diff): self
    {
         $this->diff = $diff;

        return $this;
    }

    public function getType(): string|null
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }


}
