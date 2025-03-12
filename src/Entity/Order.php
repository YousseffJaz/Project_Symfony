<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $identifier;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="float")
     */
    private $total;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $shippingCost;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount;

    /**
     * @ORM\Column(type="float")
     */
    private $paid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $paymentType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $paymentMethod;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $admin;

    /**
     * @ORM\OneToMany(targetEntity=LineItem::class, mappedBy="orderItem", orphanRemoval=true)
     */
    private $lineItems;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $shopifyNote;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orderStatus;

    /**
     * @ORM\OneToMany(targetEntity=Upload::class, mappedBy="invoice", orphanRemoval=true)
     */
    private $uploads;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $option1;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $option2;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $option3;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $option4;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $option5;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $option6;

    /**
     * @ORM\ManyToOne(targetEntity=Note::class, inversedBy="invoice")
     */
    private $note2;

    /**
     * @ORM\OneToMany(targetEntity=OrderHistory::class, mappedBy="invoice")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $orderHistories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shopifyId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shopifyOrderId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $trackingId;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="invoice")
     */
    private $notifications;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class, inversedBy="deliveryOrders")
     * @ORM\JoinColumn(nullable=true)
     */
    private $delivery;

    public function __construct()
    {
        $this->lineItems = new ArrayCollection();
        $this->createdAt = new \DateTime('now', timezone_open('Europe/Paris'));
        $this->uploads = new ArrayCollection();
        $this->orderHistories = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

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

    public function getPaid(): ?float
    {
        return $this->paid;
    }

    public function setPaid(float $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getPaymentType(): ?int
    {
        return $this->paymentType;
    }

    public function setPaymentType(?int $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getPaymentMethod(): ?int
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?int $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

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
            $lineItem->setOrderItem($this);
        }

        return $this;
    }

    public function removeLineItem(LineItem $lineItem): self
    {
        if ($this->lineItems->removeElement($lineItem)) {
            // set the owning side to null (unless already changed)
            if ($lineItem->getOrderItem() === $this) {
                $lineItem->setOrderItem(null);
            }
        }

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

    public function getShopifyNote(): ?string
    {
        return $this->shopifyNote;
    }

    public function setShopifyNote(?string $shopifyNote): self
    {
        $this->shopifyNote = $shopifyNote;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getOrderStatus(): ?int
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(?int $orderStatus): self
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * @return Collection|Upload[]
     */
    public function getUploads(): Collection
    {
        return $this->uploads;
    }

    public function addUpload(Upload $upload): self
    {
        if (!$this->uploads->contains($upload)) {
            $this->uploads[] = $upload;
            $upload->setInvoice($this);
        }

        return $this;
    }

    public function removeUpload(Upload $upload): self
    {
        if ($this->uploads->removeElement($upload)) {
            // set the owning side to null (unless already changed)
            if ($upload->getInvoice() === $this) {
                $upload->setInvoice(null);
            }
        }

        return $this;
    }

    public function getOption1(): ?bool
    {
        return $this->option1;
    }

    public function setOption1(?bool $option1): self
    {
        $this->option1 = $option1;

        return $this;
    }

    public function getOption2(): ?bool
    {
        return $this->option2;
    }

    public function setOption2(?bool $option2): self
    {
        $this->option2 = $option2;

        return $this;
    }

    public function getOption3(): ?bool
    {
        return $this->option3;
    }

    public function setOption3(?bool $option3): self
    {
        $this->option3 = $option3;

        return $this;
    }

    public function getOption4(): ?bool
    {
        return $this->option4;
    }

    public function setOption4(?bool $option4): self
    {
        $this->option4 = $option4;

        return $this;
    }

    public function getOption5(): ?bool
    {
        return $this->option5;
    }

    public function setOption5(?bool $option5): self
    {
        $this->option5 = $option5;

        return $this;
    }

    public function getOption6(): ?bool
    {
        return $this->option6;
    }

    public function setOption6(?bool $option6): self
    {
        $this->option6 = $option6;

        return $this;
    }

    public function getNote2(): ?Note
    {
        return $this->note2;
    }

    public function setNote2(?Note $note2): self
    {
        $this->note2 = $note2;

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
            // set the owning side to null (unless already changed)
            if ($orderHistory->getInvoice() === $this) {
                $orderHistory->setInvoice(null);
            }
        }

        return $this;
    }

    public function getShopifyId(): ?string
    {
        return $this->shopifyId;
    }

    public function setShopifyId(?string $shopifyId): self
    {
        $this->shopifyId = $shopifyId;

        return $this;
    }

    public function getShopifyOrderId(): ?string
    {
        return $this->shopifyOrderId;
    }

    public function setShopifyOrderId(?string $shopifyOrderId): self
    {
        $this->shopifyOrderId = $shopifyOrderId;

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

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setInvoice($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getInvoice() === $this) {
                $notification->setInvoice(null);
            }
        }

        return $this;
    }

    public function getDelivery(): ?Admin
    {
        return $this->delivery;
    }

    public function setDelivery(?Admin $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }
}
