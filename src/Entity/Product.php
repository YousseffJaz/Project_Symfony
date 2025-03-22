<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $price = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $archive = false;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'product')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $alert = 10;

    #[ORM\Column(type: 'float', nullable: true)]
    private float $purchasePrice = 0.0;

    #[ORM\OneToMany(targetEntity: Variant::class, mappedBy: 'product')]
    private Collection $variants;

    #[ORM\OneToMany(targetEntity: StockList::class, mappedBy: 'product')]
    private Collection $stockLists;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $digital = false;

    #[ORM\OneToMany(targetEntity: LineItem::class, mappedBy: 'product')]
    private Collection $lineItems;

    public function __construct()
    {
        $this->archive = false;
        $this->purchasePrice = 0;
        $this->alert = 10;
        $this->variants = new ArrayCollection();
        $this->stockLists = new ArrayCollection();
        $this->lineItems = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        // Si la nouvelle catégorie est la même que l'ancienne, ne rien faire
        if ($this->category === $category) {
            return $this;
        }

        // Gérer l'ancienne catégorie si elle existe
        if ($this->category !== null) {
            $oldCategory = $this->category;
            $this->category = null;
            $oldCategory->removeProduct($this);
        }

        // Gérer la nouvelle catégorie
        $this->category = $category;

        // Si une nouvelle catégorie est définie, ajouter ce produit à sa collection
        if ($category !== null && !$category->getProduct()->contains($this)) {
            $category->addProduct($this);
        }

        return $this;
    }

    public function getAlert(): ?int
    {
        return $this->alert;
    }

    public function setAlert(?int $alert): self
    {
        $this->alert = $alert;

        return $this;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(?float $purchasePrice): self
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    /**
     * @return Collection|Variant[]
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(Variant $variant): self
    {
        if (!$this->variants->contains($variant)) {
            $this->variants[] = $variant;
            $variant->setProduct($this);
        }

        return $this;
    }

    public function removeVariant(Variant $variant): self
    {
        if ($this->variants->removeElement($variant)) {
            // set the owning side to null (unless already changed)
            if ($variant->getProduct() === $this) {
                $variant->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StockList[]
     */
    public function getStockLists(): Collection
    {
        return $this->stockLists;
    }

    public function addStockList(StockList $stockList): self
    {
        if (!$this->stockLists->contains($stockList)) {
            $this->stockLists[] = $stockList;
            $stockList->setProduct($this);
        }

        return $this;
    }

    public function removeStockList(StockList $stockList): self
    {
        if ($this->stockLists->removeElement($stockList)) {
            // set the owning side to null (unless already changed)
            if ($stockList->getProduct() === $this) {
                $stockList->setProduct(null);
            }
        }

        return $this;
    }

    public function getDigital(): ?bool
    {
        return $this->digital;
    }

    public function setDigital(?bool $digital): self
    {
        $this->digital = $digital;

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
            $lineItem->setProduct($this);
        }

        return $this;
    }

    public function removeLineItem(LineItem $lineItem): self
    {
        if ($this->lineItems->removeElement($lineItem)) {
            // set the owning side to null (unless already changed)
            if ($lineItem->getProduct() === $this) {
                $lineItem->setProduct(null);
            }
        }

        return $this;
    }
}
