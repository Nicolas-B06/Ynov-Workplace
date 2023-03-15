<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
#[ApiResource(
    // Par défaut, l'utilisateur n'a accès aux endpoints que si sont role est ROLE_USER ou plus
    security: "is_granted('ROLE_USER')",
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(
            // On vérifie que l'utilisateur est le propriétaire de la resource ou qu'il est admin
            security: "is_granted('ROLE_ADMIN') or object.owner == user"
        ),
        new Delete(
            // On vérifie que l'utilisateur est le propriétaire de la resource ou qu'il est admin
            security: "is_granted('ROLE_ADMIN') or object.owner == user"
        ),
    ],
    normalizationContext: ['groups' => ['group:read']],
    denormalizationContext: ['groups' => ['group:write']],
)]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['group:read'])]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[Groups(['group:write', 'group:read'])]
    private ?string $name = null;


    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['group:write', 'group:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['group:write', 'group:read'])]
    private ?string $picture = null;

    #[ORM\Column]
    private ?bool $isDeleted = null;

    #[ORM\Column]
    #[Groups(['group:read'])]
    private ?\DateTimeImmutable $createdAt = null;


    #[ORM\ManyToOne(inversedBy: 'ownedGroups')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['group:read'])]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'subscribedGroups')]
    private Collection $members;

    #[ORM\OneToMany(mappedBy: 'requestedGroup', targetEntity: GroupRequest::class, orphanRemoval: true)]
    private Collection $groupRequests;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->groupRequests = new ArrayCollection();

        $this->createdAt = new \DateTimeImmutable();
        $this->isDeleted = false;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }

    /**
     * @return Collection<int, GroupRequest>
     */
    public function getGroupRequests(): Collection
    {
        return $this->groupRequests;
    }

    public function addGroupRequest(GroupRequest $groupRequest): self
    {
        if (!$this->groupRequests->contains($groupRequest)) {
            $this->groupRequests->add($groupRequest);
            $groupRequest->setRequestedGroup($this);
        }

        return $this;
    }

    public function removeGroupRequest(GroupRequest $groupRequest): self
    {
        if ($this->groupRequests->removeElement($groupRequest)) {
            // set the owning side to null (unless already changed)
            if ($groupRequest->getRequestedGroup() === $this) {
                $groupRequest->setRequestedGroup(null);
            }
        }

        return $this;
    }
}
