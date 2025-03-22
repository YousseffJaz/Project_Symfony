<?php

namespace App\Entity;

use App\Entity\Role;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\AdminRepository;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['email'],
    message: 'Un utilisateur s\'est déjà inscrit avec cette adresse email'
)]
class Admin implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\Email(message: 'L\'adresse mail est invalide !')]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $hash;

    #[Assert\EqualTo(propertyPath: 'hash', message: 'Les mots de passes sont différents !')]
    public ?string $passwordConfirm = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $archive = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $statistics = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $invoices = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $histories = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $products = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $accounting = false;

    #[ORM\OneToMany(mappedBy: 'admin', targetEntity: Order::class, orphanRemoval: true)]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'admin', targetEntity: OrderHistory::class)]
    private Collection $orderHistories;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $priceList = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stockList = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $archivedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->archive = false;
        $this->statistics = false;
        $this->invoices = false;
        $this->histories = false;
        $this->products = false;
        $this->accounting = false;
        $this->orderHistories = new ArrayCollection();
    }

    public function getClassName(): string {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_ADMIN'];
        if ($this->role) {
            $roles[] = $this->role;
        }
        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return $this->hash;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        if ($this->archivedAt !== null) {
            $this->isActive = false;
        }
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setAdmin($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getAdmin() === $this) {
                $order->setAdmin(null);
            }
        }

        return $this;
    }

    public function isArchive(): bool
    {
        return $this->archive;
    }

    public function setArchive(bool $archive): self
    {
        $this->archive = $archive;

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
            $orderHistory->setAdmin($this);
        }

        return $this;
    }

    public function removeOrderHistory(OrderHistory $orderHistory): self
    {
        if ($this->orderHistories->removeElement($orderHistory)) {
            // set the owning side to null (unless already changed)
            if ($orderHistory->getAdmin() === $this) {
                $orderHistory->setAdmin(null);
            }
        }

        return $this;
    }

    public function getPriceList(): ?string
    {
        return $this->priceList;
    }

    public function setPriceList(?string $priceList): self
    {
        $this->priceList = $priceList;

        return $this;
    }

    public function getStockList(): ?string
    {
        return $this->stockList;
    }

    public function setStockList(?string $stockList): self
    {
        $this->stockList = $stockList;

        return $this;
    }

    public function isStatistics(): bool
    {
        return $this->statistics;
    }

    public function setStatistics(bool $statistics): self
    {
        $this->statistics = $statistics;

        return $this;
    }

    public function isInvoices(): bool
    {
        return $this->invoices;
    }

    public function setInvoices(bool $invoices): self
    {
        $this->invoices = $invoices;

        return $this;
    }

    public function isHistories(): bool
    {
        return $this->histories;
    }

    public function setHistories(bool $histories): self
    {
        $this->histories = $histories;

        return $this;
    }

    public function isProducts(): bool
    {
        return $this->products;
    }

    public function setProducts(bool $products): self
    {
        $this->products = $products;

        return $this;
    }

    public function isAccounting(): bool
    {
        return $this->accounting;
    }

    public function setAccounting(bool $accounting): self
    {
        $this->accounting = $accounting;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(?\DateTimeImmutable $archivedAt): self
    {
        $this->archivedAt = $archivedAt;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
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
}
