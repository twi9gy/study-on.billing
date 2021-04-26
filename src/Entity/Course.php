<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CourseRepository::class)
 */
class Course
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $code;

    private const COURSE_TYPES = [
        1 => 'rent',
        2 => 'free',
        3 => 'buy'
    ];

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $cost;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="course")
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTypeFormatNumber(): ?int
    {
        return $this->type;
    }

    public function getTypeFormatString(): ?string
    {
        return self::COURSE_TYPES[$this->type];
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(?float $cost): self
    {
        $this->cost = $cost;

        return $this;
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
            $transaction->setCourse($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        // set the owning side to null (unless already changed)
        if ($this->transactions->removeElement($transaction) && $transaction->getCourse() === $this) {
            $transaction->setCourse(null);
        }

        return $this;
    }
}
