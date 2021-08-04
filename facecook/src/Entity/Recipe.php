<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RecipeRepository::class)
 */
class Recipe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $slug;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="recipes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $category;


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="recipes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $instructions;

    /**
     * @ORM\Column(type="text")
     * @Groups({"browse_recipes", "read_recipes"})
     */
    private $ingredients;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="visibleRecipes")
     */
    private $visibleBy;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->status = 1;
        $this->visibleBy = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }

    public function setIngredients(string $ingredients): self
    {
        $this->ingredients = $ingredients;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getVisibleBy(): Collection
    {
        return $this->visibleBy;
    }

    public function addVisibleBy(User $visibleBy): self
    {
        // we check if the user $visibleBy is not already in the property visibleBy
        // and if the recipe's status is 3 (custom)
        // and if the $visibleBy is a friend of the recipe's user
        if (!$this->visibleBy->contains($visibleBy) && $this->status == 3 && $this->user->getMyfriends()->contains($visibleBy)) {
            $this->visibleBy[] = $visibleBy;
        }

        return $this;
    }

    public function removeVisibleBy(User $visibleBy): self
    {
        $this->visibleBy->removeElement($visibleBy);

        return $this;
    }
}
