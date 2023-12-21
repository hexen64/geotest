<?php

namespace App\Entity;

use App\Repository\VariantsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(type: "boolean", options: ["default" => 1])]
    private $visible;

    #[ORM\Column(type: "text")]
    private $description;

    #[ORM\ManyToMany(targetEntity: Rows::class, inversedBy: "variants")]
    #[ORM\JoinTable(name: "variants_rows")]
    #[ORM\JoinColumn(name: "variant_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "row_id", referencedColumnName: "id")]
    private Collection $rows;

    public function __construct()
    {
        $this->rows = new ArrayCollection();
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

    #[ORM\OneToMany(targetEntity: VariantsRows::class, mappedBy: "variant", cascade: ["persist"])]
    private Collection $variantsRows;


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
            // Set the owning side to null (unless already changed)
            if ($variantsRow->getVariant() === $this) {
                $variantsRow->setVariant(null);
            }

        return $this;
    }
}
