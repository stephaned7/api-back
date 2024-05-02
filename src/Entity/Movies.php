<?php

namespace App\Entity;

use App\Repository\MoviesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoviesRepository::class)]
class Movies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("movie:read")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups("movie:read")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups("movie:read")]
    private ?string $synopsis = null;

    #[ORM\Column]
    #[Groups("movie:read")]
    private ?int $release_date = null;

    #[ORM\Column(length: 255)]
    #[Groups("movie:read")]
    private ?string $director = null;

    /**
     * @var Collection<int, Categories>
     */
    #[ORM\ManyToMany(targetEntity: Categories::class, mappedBy: 'movies')]
    #[Groups("movie:read")]
    private Collection $categories;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'movie_likes')]
    private Collection $users;
    
    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getReleaseDate(): ?int
    {
        return $this->release_date;
    }

    public function setReleaseDate(int $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(string $director): static
    {
        $this->director = $director;

        return $this;
    }

    /**
     * @return Collection<int, Categories>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Categories $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addMovie($this);
        }

        return $this;
    }

    public function removeCategory(Categories $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeMovie($this);
        }

        return $this;
    }

    public function addMovieLike(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    
        return $this;
    }
    
    public function removeMovieLike(User $user): static
    {
        $this->users->removeElement($user);
    
        return $this;
    }

    public function isLikedByUser(User $user): bool
    {
        return $this->users->contains($user);
    }

    public function getLikesCount(): int
    {
        return $this->users->count();
    }
}
