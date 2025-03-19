<?php

namespace App\Entity;

use App\Entity\Role;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdminRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Un utilisateur s'est déjà inscrit avec cette adresse email"
 * )
 */
class Admin implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="L'adresse mail est invalide !")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hash;

    /**
     * @Assert\EqualTo(propertyPath="hash", message="Les mots de passes sont différents !")
     */
    public $passwordConfirm;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="admin", orphanRemoval=true)
     */
    private $orders;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archive;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="admin")
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="createdBy")
     * @ORM\JoinColumn(nullable=false)
     */
    private $taskBy;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="completeBy")
     */
    private $tasksComplete;

    /**
     * @ORM\OneToMany(targetEntity=OrderHistory::class, mappedBy="admin")
     */
    private $orderHistories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $priceList;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stockList;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="admin")
     */
    private $notifications;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $statistics;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $invoices;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $histories;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $folders;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $products;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accounting;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="delivery")
     */
    private $deliveryOrders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->taskBy = new ArrayCollection();
        $this->tasksComplete = new ArrayCollection();
        $this->archive = false;
        $this->statistics = false;
        $this->invoices = false;
        $this->histories = false;
        $this->folders = false;
        $this->products = false;
        $this->accounting = false;
        $this->orderHistories = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->deliveryOrders = new ArrayCollection();
    }

    public function getClassName() {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

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

    public function getHash(): ?string
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

    public function getPassword(): ?string
    {
        return $this->hash;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials(): void {}

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

    public function getArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(?bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setAdmin($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getAdmin() === $this) {
                $task->setAdmin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTaskBy(): Collection
    {
        return $this->taskBy;
    }

    public function addTaskBy(Task $taskBy): self
    {
        if (!$this->taskBy->contains($taskBy)) {
            $this->taskBy[] = $taskBy;
            $taskBy->setCreatedBy($this);
        }

        return $this;
    }

    public function removeTaskBy(Task $taskBy): self
    {
        if ($this->taskBy->removeElement($taskBy)) {
            // set the owning side to null (unless already changed)
            if ($taskBy->getCreatedBy() === $this) {
                $taskBy->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasksComplete(): Collection
    {
        return $this->tasksComplete;
    }

    public function addTasksComplete(Task $tasksComplete): self
    {
        if (!$this->tasksComplete->contains($tasksComplete)) {
            $this->tasksComplete[] = $tasksComplete;
            $tasksComplete->setCompleteBy($this);
        }

        return $this;
    }

    public function removeTasksComplete(Task $tasksComplete): self
    {
        if ($this->tasksComplete->removeElement($tasksComplete)) {
            // set the owning side to null (unless already changed)
            if ($tasksComplete->getCompleteBy() === $this) {
                $tasksComplete->setCompleteBy(null);
            }
        }

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
            $notification->setAdmin($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getAdmin() === $this) {
                $notification->setAdmin(null);
            }
        }

        return $this;
    }

    public function getStatistics(): ?bool
    {
        return $this->statistics;
    }

    public function setStatistics(?bool $statistics): self
    {
        $this->statistics = $statistics;

        return $this;
    }

    public function getInvoices(): ?bool
    {
        return $this->invoices;
    }

    public function setInvoices(?bool $invoices): self
    {
        $this->invoices = $invoices;

        return $this;
    }

    public function getHistories(): ?bool
    {
        return $this->histories;
    }

    public function setHistories(?bool $histories): self
    {
        $this->histories = $histories;

        return $this;
    }

    public function getFolders(): ?bool
    {
        return $this->folders;
    }

    public function setFolders(?bool $folders): self
    {
        $this->folders = $folders;

        return $this;
    }

    public function getProducts(): ?bool
    {
        return $this->products;
    }

    public function setProducts(?bool $products): self
    {
        $this->products = $products;

        return $this;
    }

    public function getAccounting(): ?bool
    {
        return $this->accounting;
    }

    public function setAccounting(?bool $accounting): self
    {
        $this->accounting = $accounting;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getDeliveryOrders(): Collection
    {
        return $this->deliveryOrders;
    }

    public function addDeliveryOrder(Order $deliveryOrder): self
    {
        if (!$this->deliveryOrders->contains($deliveryOrder)) {
            $this->deliveryOrders[] = $deliveryOrder;
            $deliveryOrder->setDelivery($this);
        }

        return $this;
    }

    public function removeDeliveryOrder(Order $deliveryOrder): self
    {
        if ($this->deliveryOrders->removeElement($deliveryOrder)) {
            // set the owning side to null (unless already changed)
            if ($deliveryOrder->getDelivery() === $this) {
                $deliveryOrder->setDelivery(null);
            }
        }

        return $this;
    }
}
