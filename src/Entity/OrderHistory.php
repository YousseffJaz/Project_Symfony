<?php

namespace App\Entity;

use App\Repository\OrderHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderHistoryRepository::class)]
class OrderHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderHistories')]
    private ?Order $invoice = null;

    #[ORM\ManyToOne(targetEntity: Admin::class, inversedBy: 'orderHistories')]
    private ?Admin $admin = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', timezone_open('Europe/Paris'));
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getInvoice(): ?Order
    {
        return $this->invoice;
    }

    public function setInvoice(?Order $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }
}
