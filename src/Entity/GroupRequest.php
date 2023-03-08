<?php

namespace App\Entity;

use App\Repository\GroupRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GroupRequestRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['groupRequest:read']],
    denormalizationContext: ['groups' => ['groupRequest:write']],
)]
class GroupRequest
{
    #[Groups(['groupRequest:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['groupRequest:read'])]
    #[ORM\ManyToOne(inversedBy: 'groupRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requestingUser = null;

    #[Groups(['groupRequest:read', 'groupRequest:write'])]
    #[ORM\ManyToOne(inversedBy: 'groupRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Group $requestedGroup = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestingUser(): ?User
    {
        return $this->requestingUser;
    }

    public function setRequestingUser(?User $requestingUser): self
    {
        $this->requestingUser = $requestingUser;

        return $this;
    }

    public function getRequestedGroup(): ?Group
    {
        return $this->requestedGroup;
    }

    public function setRequestedGroup(?Group $requestedGroup): self
    {
        $this->requestedGroup = $requestedGroup;

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
}
