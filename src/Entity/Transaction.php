<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    private const OPERATION_TYPES = [
        1 => 'payment',
        2 => 'deposit',
    ];

    /**
     * @ORM\Column(type="smallint")
     */
    private $typeOperation;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $periodValidity;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, inversedBy="transactions")
     */
    private $course;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userBilling;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    { }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeOperationFormatNumber(): ?int
    {
        return $this->typeOperation;
    }

    public function getTypeOperationFormatString(): ?string
    {
        return self::OPERATION_TYPES[$this->typeOperation];
    }

    public function setTypeOperation(int $typeOperation): self
    {
        $this->typeOperation = $typeOperation;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(?float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getPeriodValidity(): ?\DateTimeInterface
    {
        return $this->periodValidity;
    }

    public function setPeriodValidity(\DateTimeInterface $periodValidity): self
    {
        $this->periodValidity = $periodValidity;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getUserBilling(): ?User
    {
        return $this->userBilling;
    }

    public function setUserBilling(?User $userBilling): self
    {
        $this->userBilling = $userBilling;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
