<?php

namespace App\Entity;

use App\Model\UserDto;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="User model",
 *     description="User model"
 * )
 *
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="billing_user")
 */
class User implements UserInterface
{
    /**
     * @OA\Property(
     *     format="int64",
     *     title="Id",
     *     description="Id"
     * )
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @OA\Property(
     *     format="email",
     *     title="Email",
     *     description="Email"
     * )
     *
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @OA\Property(
     *     format="array",
     *     title="Roles",
     *     description="Roles"
     * )
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @OA\Property(
     *     format="string",
     *     title="Password",
     *     description="Password"
     * )
     *
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @OA\Property(
     *     format="float",
     *     title="Balance",
     *     description="Balance"
     * )
     *
     * @ORM\Column(type="float")
     */
    private $balance;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="userBilling", orphanRemoval=true)
     */
    private $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
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

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): self
    {
        $this->balance = $balance;
        return $this;
    }

    public static function fromDto(UserDto $userDto): self
    {
        $user = new self();

        $user->setEmail($userDto->email);
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($userDto->password);
        $user->setBalance(0);

        return $user;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setUserBilling($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        // set the owning side to null (unless already changed)
        if ($this->transactions->removeElement($transaction) && $transaction->getUserBilling() === $this) {
            $transaction->setUserBilling(null);
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }
}
