<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 25)]
    private ?string $firstname = null;

    #[ORM\Column(length: 25)]
    private ?string $lastname = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 25, unique: true)]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Topic::class)]
    private Collection $topics;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class)]
    private Collection $posts;

    #[ORM\Column(type: "boolean")]
    private bool $isVerified = false;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $passwordResetToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $passwordResetTokenExpiresAt = null;

    #[ORM\ManyToMany(targetEntity: Community::class, mappedBy: 'members')]
    private Collection $joinedCommunities;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Discussion::class)]
    private Collection $discussions;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Community::class)]
    private Collection $createdCommunities;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->discussions = new ArrayCollection();
        $this->joinedCommunities = new ArrayCollection();
        $this->createdCommunities = new ArrayCollection();
        $this->setProfilePicture('default-profile.png');
        $this->createdAt = new \DateTime();
        $this->roles = ['ROLE_USER']; // Default role
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;
        return $this;
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // Guarantee every user has ROLE_USER
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear sensitive data here if needed
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getProfilePicture(): ?string
    {
    if ($this->profilePicture === 'default-profile.png') {
        // Path for the default profile picture
        return '/images/default-profile.png';
    }

    // Path for user-uploaded profile pictures
    return '/uploads/profile_pictures/' . $this->profilePicture;
    }

    
    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }


    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(?string $passwordResetToken): self
    {
        $this->passwordResetToken = $passwordResetToken;
        return $this;
    }

    public function getPasswordResetTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->passwordResetTokenExpiresAt;
    }

    public function setPasswordResetTokenExpiresAt(?\DateTimeInterface $passwordResetTokenExpiresAt): self
    {
        $this->passwordResetTokenExpiresAt = $passwordResetTokenExpiresAt;
        return $this;
    }
    
    /**
     * @return Collection<int, Community>
     */
    public function getJoinedCommunities(): Collection
    {
        return $this->joinedCommunities;
    }

    public function joinCommunity(Community $community): self
    {
        if (!$this->joinedCommunities->contains($community)) {
            $this->joinedCommunities->add($community);
            $community->addMember($this);
        }
        return $this;
    }

    public function leaveCommunity(Community $community): self
    {
        if ($this->joinedCommunities->removeElement($community)) {
            $community->removeMember($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Discussion>
     */
    public function getDiscussions(): Collection
    {
        return $this->discussions;
    }

    public function addDiscussion(Discussion $discussion): self
    {
        if (!$this->discussions->contains($discussion)) {
            $this->discussions->add($discussion);
            $discussion->setAuthor($this);
        }
        return $this;
    }

    public function removeDiscussion(Discussion $discussion): self
    {
        if ($this->discussions->removeElement($discussion)) {
            if ($discussion->getAuthor() === $this) {
                $discussion->setAuthor(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Community>
     */
    public function getCreatedCommunities(): Collection
    {
        return $this->createdCommunities;
    }

    public function addCreatedCommunity(Community $community): self
    {
        if (!$this->createdCommunities->contains($community)) {
            $this->createdCommunities->add($community);
            $community->setCreator($this);
        }
        return $this;
    }

    public function removeCreatedCommunity(Community $community): self
    {
        if ($this->createdCommunities->removeElement($community)) {
            if ($community->getCreator() === $this) {
                $community->setCreator(null);
            }
        }
        return $this;
    }

    // Uncomment and fix the Topics and Posts methods as they might be needed
    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): self
    {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
            $topic->setAuthor($this);
        }
        return $this;
    }

    public function removeTopic(Topic $topic): self
    {
        if ($this->topics->removeElement($topic)) {
            if ($topic->getAuthor() === $this) {
                $topic->setAuthor(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }
        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }
        return $this;
    }

    // Add new methods for admin functionality
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }

    public function isModerator(): bool
    {
        return in_array('ROLE_MODERATOR', $this->roles, true) || $this->isAdmin();
    }

    public function addRole(string $role): self
    {
        $role = strtoupper($role);
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): self
    {
        $role = strtoupper($role);
        if (($key = array_search($role, $this->roles, true)) !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles); // Reindex array
        }
        return $this;
    }


}
