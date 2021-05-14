<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"browse_recipes", "browse_users", "read_users"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"browse_recipes", "browse_users", "read_users"})
     */
    private $pseudonym;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"browse_users", "read_users"})
     */
    private $avatar;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"browse_users", "read_users"})
     */
    private $status;


    /**
     * @ORM\OneToMany(targetEntity=Recipe::class, mappedBy="user")
     */
    private $recipes;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="friendsWithMe")
     * @ORM\JoinTable(name="friends",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="friend_user_id", referencedColumnName="id")}
     *      )
     */
    private $myfriends;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="myfriends")
     */
    private $friendsWithMe;

    /**
     * @ORM\ManyToMany(targetEntity=Recipe::class, mappedBy="visibleBy")
     */
    private $visibleRecipes;

    public function __construct()
    {
        $this->roles[] = "ROLE_USER";
        $this->status = 1;
        $this->recipes = new ArrayCollection();
        $this->myfriends = new ArrayCollection();
        $this->friendsWithMe = new ArrayCollection();
        $this->visibleRecipes = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->pseudonym;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudonym(): ?string
    {
        return $this->pseudonym;
    }

    public function setPseudonym(string $pseudonym): self
    {
        $this->pseudonym = $pseudonym;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

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
     * @return Collection|Recipe[]
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): self
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes[] = $recipe;
            $recipe->setUser($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getUser() === $this) {
                $recipe->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getMyfriends(): Collection
    {
        return $this->myfriends;
    }

    /**
     * @Groups({"read_users"})
     */
    public function getFriends()
    {
        $friends = [];
        foreach($this->myfriends as $friend) {
            $friends[] = [
                'id' => $friend->getId(),
                'pseudonym' => $friend->getPseudonym(),
                'avatar' => $friend->getAvatar(),
            ];
        }

        return $friends;
    }
    public function addMyfriend(self $myfriend): self
    {
        if (!$this->myfriends->contains($myfriend)) {
            $this->myfriends[] = $myfriend;
        }

        return $this;
    }

    public function removeMyfriend(self $myfriend): self
    {
        $this->myfriends->removeElement($myfriend);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getFriendsWithMe(): Collection
    {
        return $this->friendsWithMe;
    }

    public function addFriendsWithMe(self $friendsWithMe): self
    {
        if (!$this->friendsWithMe->contains($friendsWithMe)) {
            $this->friendsWithMe[] = $friendsWithMe;
            $friendsWithMe->addMyfriend($this);
        }

        return $this;
    }

    public function removeFriendsWithMe(self $friendsWithMe): self
    {
        if ($this->friendsWithMe->removeElement($friendsWithMe)) {
            $friendsWithMe->removeMyfriend($this);
        }

        return $this;
    }

    /**
     * @return Collection|Recipe[]
     */
    public function getVisibleRecipes(): Collection
    {
        return $this->visibleRecipes;
    }

    /**
     * @Groups({"read_users"})
     */
    public function getMyVisibleRecipes()
    {
        $myVisibleRecipes = [];
        foreach ($this->visibleRecipes as $visibleRecipe) {
            $myVisibleRecipes[] = [
                'id' => $visibleRecipe->getId(),
                'title' => $visibleRecipe->getTitle(),
            ];
        }

        return $myVisibleRecipes;
    }

    public function addVisibleRecipe(Recipe $visibleRecipe): self
    {
        if (!$this->visibleRecipes->contains($visibleRecipe)) {
            $this->visibleRecipes[] = $visibleRecipe;
            $visibleRecipe->addVisibleBy($this);
        }

        return $this;
    }

    public function removeVisibleRecipe(Recipe $visibleRecipe): self
    {
        if ($this->visibleRecipes->removeElement($visibleRecipe)) {
            $visibleRecipe->removeVisibleBy($this);
        }

        return $this;
    }
}
