<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use App\State\UserPasswordHasher;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\SelfInfoController;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    // Par défaut, l'utilisateur n'a accès aux endpoints que si sont role est ROLE_USER ou plus
    security: "is_granted('ROLE_USER')",
    operations: [
        new GetCollection(),
        new Post(
            processor: UserPasswordHasher::class,
            validationContext: ['groups' => ['Default', 'user:create']]
        ),
        new Get(),
        new Patch(
            processor: UserPasswordHasher::class,
            // On vérifie que l'utilisateur est admin ou qu'il est l'utilisateur ciblé
            security: "is_granted('ROLE_ADMIN') or object == user"
        ),
        new Delete(
            // On vérifie que l'utilisateur est admin ou qu'il est l'utilisateur ciblé
            security: "is_granted('ROLE_ADMIN') or object == user"
        ),
        new Get(
            uriTemplate: '/users/{id}/info',
            name: 'self_info',
            controller: SelfInfoController::class,
            normalizationContext: ['groups' => 'user:nickname'],
            security: "is_granted('ROLE_USER')"
        )
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create', 'user:update']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['user:create'])]
    #[Groups(['user:create', 'user:update'])]
    private ?string $plainPassword = null;

    #[Groups(['user:read', 'user:create', 'user:update', 'user:nickname'])]
    #[ORM\Column(length: 20)]
    private ?string $nickname = null;

    #[ORM\Column]
    private ?bool $isDeleted = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Group::class, orphanRemoval: true)]
    private Collection $ownedGroups;

    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'members')]
    private Collection $subscribedGroups;

    #[ORM\OneToMany(mappedBy: 'requestingUser', targetEntity: GroupRequest::class, orphanRemoval: true)]
    private Collection $groupRequests;

    public function __construct()
    {
        $this->ownedGroups = new ArrayCollection();
        $this->subscribedGroups = new ArrayCollection();
        $this->groupRequests = new ArrayCollection();

        $this->isDeleted = false;
        $this->createdAt = new \DateTimeImmutable();
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
    public function getUserIdentifier(): string
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

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

    /**
     * @return Collection<int, Group>
     */
    public function getOwnedGroups(): Collection
    {
        return $this->ownedGroups;
    }

    public function addOwnedGroup(Group $ownedGroup): self
    {
        if (!$this->ownedGroups->contains($ownedGroup)) {
            $this->ownedGroups->add($ownedGroup);
            $ownedGroup->setOwner($this);
        }

        return $this;
    }

    public function removeOwnedGroup(Group $ownedGroup): self
    {
        if ($this->ownedGroups->removeElement($ownedGroup)) {
            // set the owning side to null (unless already changed)
            if ($ownedGroup->getOwner() === $this) {
                $ownedGroup->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getSubscribedGroups(): Collection
    {
        return $this->subscribedGroups;
    }

    public function addSubscribedGroup(Group $subscribedGroup): self
    {
        if (!$this->subscribedGroups->contains($subscribedGroup)) {
            $this->subscribedGroups->add($subscribedGroup);
            $subscribedGroup->addMember($this);
        }

        return $this;
    }

    public function removeSubscribedGroup(Group $subscribedGroup): self
    {
        if ($this->subscribedGroups->removeElement($subscribedGroup)) {
            $subscribedGroup->removeMember($this);
        }

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
            $groupRequest->setRequestingUser($this);
        }

        return $this;
    }

    public function removeGroupRequest(GroupRequest $groupRequest): self
    {
        if ($this->groupRequests->removeElement($groupRequest)) {
            // set the owning side to null (unless already changed)
            if ($groupRequest->getRequestingUser() === $this) {
                $groupRequest->setRequestingUser(null);
            }
        }

        return $this;
    }
}
