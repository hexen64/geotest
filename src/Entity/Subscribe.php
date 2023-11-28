<?php

namespace App\Entity;

use App\Repository\SubscribeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscribeRepository::class)]
#[ORM\Table(name: '`subscribe`')]
class Subscribe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255)]
    private $email;

    #[ORM\Column(type: "string", length: 255)]
    private $name;

    #[ORM\Column(type: "datetime", name: "create_dt")]
    private $createDt;

    #[ORM\Column(type: "datetime", name: "update_dt")]
    private $updateDt;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $active;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $confirmed;

    #[ORM\Column(type: "string", length: 32, name: "confirm_code")]
    private $confirmCode;

    #[ORM\Column(type: "datetime", name: "confirm_expire")]
    private $confirmExpire;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreateDt(): ?\DateTimeInterface
    {
        return $this->createDt;
    }

    public function setCreateDt(\DateTimeInterface $createDt): self
    {
        $this->createDt = $createDt;

        return $this;
    }

    public function getUpdateDt(): ?\DateTimeInterface
    {
        return $this->updateDt;
    }

    public function setUpdateDt(\DateTimeInterface $updateDt): self
    {
        $this->updateDt = $updateDt;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getConfirmCode(): ?string
    {
        return $this->confirmCode;
    }

    public function setConfirmCode(string $confirmCode): self
    {
        $this->confirmCode = $confirmCode;

        return $this;
    }

    public function getConfirmExpire(): ?\DateTimeInterface
    {
        return $this->confirmExpire;
    }

    public function setConfirmExpire(\DateTimeInterface $confirmExpire): self
    {
        $this->confirmExpire = $confirmExpire;

        return $this;
    }
}
