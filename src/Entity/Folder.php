<?php

namespace App\Entity;

use App\Repository\FolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FolderRepository::class)]
class Folder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Upload::class, mappedBy: 'folder')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $upload;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $type = null;

    public function __construct()
    {
        $this->upload = new ArrayCollection();
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
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Upload[]
     */
    public function getUpload(): Collection
    {
        return $this->upload;
    }

    public function addUpload(Upload $upload): self
    {
        if (!$this->upload->contains($upload)) {
            $this->upload[] = $upload;
            $upload->setFolder($this);
        }

        return $this;
    }

    public function removeUpload(Upload $upload): self
    {
        if ($this->upload->removeElement($upload)) {
            // set the owning side to null (unless already changed)
            if ($upload->getFolder() === $this) {
                $upload->setFolder(null);
            }
        }

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
