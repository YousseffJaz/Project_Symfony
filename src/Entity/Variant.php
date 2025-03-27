<?php

namespace App\Entity;

use App\Repository\VariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: VariantRepository::class)]
class Variant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $archive = false;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\Range(
        min: 0.01,
        max: 999999.99,
        notInRangeMessage: "Le prix doit être compris entre {{ min }}€ et {{ max }}€"
    )]
    private float $price = 0.0;

    #[ORM\OneToMany(targetEntity: LineItem::class, mappedBy: 'variant')]
    private Collection $lineItems;

    public function __construct()
    {
        $this->archive = false;
        $this->lineItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

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

    public function getArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(?bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        if ($price < 0.01 || $price > 999999.99) {
            throw new InvalidArgumentException(
                sprintf("Le prix doit être compris entre %.2f€ et %.2f€", 0.01, 999999.99)
            );
        }

        // Arrondir à 2 décimales
        $this->price = round($price, 2);

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
            $lineItem->setVariant($this);
        }

        return $this;
    }

    public function removeLineItem(LineItem $lineItem): self
    {
        if ($this->lineItems->removeElement($lineItem)) {
            // set the owning side to null (unless already changed)
            if ($lineItem->getVariant() === $this) {
                $lineItem->setVariant(null);
            }
        }

        return $this;
    }
}
