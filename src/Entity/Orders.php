<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use App\Repository\OrderssRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
#[ORM\Table(name: '`orders`')]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "text")]
    private $fio;

    #[ORM\Column(type: "string", length: 250)]
    private $phone;

    #[ORM\Column(type: "string", length: 250)]
    private $email;

    #[ORM\Column(type: "text")]
    private $firm;

    #[ORM\Column(type: "text")]
    private $address;

    #[ORM\Column(type: "text")]
    private $comment;

    #[ORM\Column(type: "string", columnDefinition: "ENUM('firm','firm+sklad','self')", length: 10, options: ["default" => "firm"])]
    private $delivery;

    #[ORM\Column(type: "float")]
    private $total;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $submit;

    // Getters and setters for the properties

    public function getId(): ?int
    {
        return $this->id;
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
}
