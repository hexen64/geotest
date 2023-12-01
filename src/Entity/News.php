<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
#[ORM\Table(name: '`news`')]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "integer")]
    private ?int $newsOrder;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $date;

    #[ORM\Column(type: "text")]
    private ?string $title;

    #[ORM\Column(type: "text")]
    private ?string $text;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $author;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $authorPosition;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $tag;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $groupId;

    // Getters and setters for the fields

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNewsOrder(): ?int
    {
        return $this->newsOrder;
    }

    public function setNewsOrder(int $newsOrder): self
    {
        $this->newsOrder = $newsOrder;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getAuthorPosition(): ?string
    {
        return $this->authorPosition;
    }

    public function setAuthorPosition(string $authorPosition): self
    {
        $this->authorPosition = $authorPosition;
        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(string $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }
}
