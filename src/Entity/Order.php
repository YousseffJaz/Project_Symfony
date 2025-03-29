<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customer $customer = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'float')]
    private float $total = 0;

    #[ORM\Column(type: 'float')]
    private float $paid = 0;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $paymentMethod = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $paymentType = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $orderStatus = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $shippingCost = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $discount = null;

    #[ORM\ManyToOne(targetEntity: Admin::class, inversedBy: 'orders')]
    private ?Admin $admin = null;

    #[ORM\OneToMany(targetEntity: LineItem::class, mappedBy: 'order', orphanRemoval: true)]
    private Collection $lineItems;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $trackingId = null;

    #[ORM\Column(type: 'integer')]
    private int $status = 0;

    #[ORM\OneToMany(targetEntity: OrderHistory::class, mappedBy: 'invoice', cascade: ['persist'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $orderHistories;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note2 = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', timezone_open('Europe/Paris'));
        $this->lineItems = new ArrayCollection();
        $this->orderHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

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

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPaid(): ?float
    {
        return $this->paid;
    }

    public function setPaid(float $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(?string $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getOrderStatus(): ?string
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(?string $orderStatus): self
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getShippingCost(): ?float
    {
        return $this->shippingCost;
    }

    public function setShippingCost(?float $shippingCost): self
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): self
    {
        $this->discount = $discount;

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

    /**
     * @return Collection|LineItem[]
     */
    public function getLineItems(): Collection
    {
        return $this->lineItems;
    }

    public function addLineItem(LineItem $lineItem): self
    {
        if (!$this->lineItems->contains($lineItem)) {
            $this->lineItems[] = $lineItem;
            $lineItem->setOrder($this);
        }

        return $this;
    }

    public function removeLineItem(LineItem $lineItem): self
    {
        if ($this->lineItems->removeElement($lineItem)) {
            if ($lineItem->getOrder() === $this) {
                $lineItem->setOrder(null);
            }
        }

        return $this;
    }

    public function getTrackingId(): ?string
    {
        return $this->trackingId;
    }

    public function setTrackingId(?string $trackingId): self
    {
        $this->trackingId = $trackingId;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|OrderHistory[]
     */
    public function getOrderHistories(): Collection
    {
        return $this->orderHistories;
    }

    public function addOrderHistory(OrderHistory $orderHistory): self
    {
        if (!$this->orderHistories->contains($orderHistory)) {
            $this->orderHistories[] = $orderHistory;
            $orderHistory->setInvoice($this);
        }

        return $this;
    }

    public function removeOrderHistory(OrderHistory $orderHistory): self
    {
        if ($this->orderHistories->removeElement($orderHistory)) {
            if ($orderHistory->getInvoice() === $this) {
                $orderHistory->setInvoice(null);
            }
        }

        return $this;
    }

    public function getNote2(): ?string
    {
        return $this->note2;
    }

    public function setNote2(?string $note2): self
    {
        $this->note2 = $note2;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->customer ? $this->customer->getFirstname() : null;
    }

    public function getLastname(): ?string
    {
        return $this->customer ? $this->customer->getLastname() : null;
    }

    public function getEmail(): ?string
    {
        return $this->customer ? $this->customer->getEmail() : null;
    }

    public function getPhone(): ?string
    {
        return $this->customer ? $this->customer->getPhone() : null;
    }

    public function getAddress(): ?string
    {
        return $this->customer ? $this->customer->getAddress() : null;
    }
}
