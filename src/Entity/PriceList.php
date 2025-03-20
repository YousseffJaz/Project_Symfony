<?php

namespace App\Entity;

use App\Repository\PriceListRepository;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PriceListRepository::class)]
class PriceList
{
    public const MAX_PRICE = 999999.99;
    public const MIN_PRICE = 0.01;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide")]
    #[Assert\Length(max: 255, maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères")]
    private ?string $title = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\Range(
        min: self::MIN_PRICE,
        max: self::MAX_PRICE,
        notInRangeMessage: "Le prix doit être compris entre {{ min }}€ et {{ max }}€"
    )]
    private float $price = 0.0;

    #[ORM\ManyToOne(targetEntity: Variant::class, inversedBy: 'priceLists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Variant $variant = null;

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
        if (empty(trim($title))) {
            throw new InvalidArgumentException("Le titre ne peut pas être vide");
        }

        if (strlen($title) > 255) {
            throw new InvalidArgumentException("Le titre ne peut pas dépasser 255 caractères");
        }

        $this->title = $title;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        if ($price < self::MIN_PRICE || $price > self::MAX_PRICE) {
            throw new InvalidArgumentException(
                sprintf("Le prix doit être compris entre %.2f€ et %.2f€", self::MIN_PRICE, self::MAX_PRICE)
            );
        }

        // Arrondir à 2 décimales
        $this->price = round($price, 2);

        return $this;
    }

    public function getVariant(): ?Variant
    {
        return $this->variant;
    }

    public function setVariant(?Variant $variant): self
    {
        $this->variant = $variant;

        return $this;
    }
}
