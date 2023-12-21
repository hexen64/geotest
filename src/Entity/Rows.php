<?php

namespace App\Entity;

use App\Repository\RowsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\ManyToMany(targetEntity: Variants::class, inversedBy: "rows")]
    #[ORM\JoinTable(name: "variants_rows")]
    #[ORM\JoinColumn(name: "row_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "variant_id", referencedColumnName: "id")]
    private Collection $variants;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->variantsRows = new ArrayCollection();
    }

    // ... getters and setters for other properties

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


    #[ORM\OneToMany(targetEntity: VariantsRows::class, mappedBy: "row", cascade: ["persist"])]
    private Collection $variantsRows;


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


}
