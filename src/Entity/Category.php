<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete()
    ],
    description: 'Une catégorie de produits',
    paginationEnabled: true,
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']]
)]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  #[Groups(['category:read'])]
  private ?int $id = null;

  #[ORM\Column(type: 'string', length: 255)]
  #[Assert\NotBlank(message: "Le nom ne peut pas être vide")]
  #[Assert\Length(
    min: 2,
    max: 255,
    minMessage: "Le nom doit faire au moins {{ limit }} caractères",
    maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
  )]
  #[Groups(['category:read', 'category:write'])]
  private ?string $name = null;

  #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'category')]
  #[Groups(['category:read'])]
  private Collection $product;

  public function __construct()
  {
    $this->product = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    if (empty(trim($name))) {
      throw new InvalidArgumentException("Le nom ne peut pas être vide");
    }

    if (strlen($name) > 255) {
      throw new InvalidArgumentException("Le nom ne peut pas dépasser 255 caractères");
    }

    $this->name = trim($name);

    return $this;
  }

  /**
   * @return Collection|Product[]
   */
  public function getProduct(): Collection
  {
    return $this->product;
  }

  public function addProduct(Product $product): self
  {
    if (!$this->product->contains($product)) {
      $this->product[] = $product;
      $product->setCategory($this);
    }

    return $this;
  }

  public function removeProduct(Product $product): self
  {
    if ($this->product->removeElement($product)) {
          // set the owning side to null (unless already changed)
      if ($product->getCategory() === $this) {
        $product->setCategory(null);
      }
    }

    return $this;
  }
}
