<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\LimitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LimitRepository::class)]
#[ORM\Table(name: '`limit`')]
#[ORM\HasLifecycleCallbacks]
class Limit
{
    use TimestampTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'limit', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private float $dailyLimit = 0.00;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private float $monthlyLimit = 0.00;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private float $dailyTotal = 0.00;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private float $monthlyTotal = 0.00;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dailyTotalDate;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private ?string $monthlyTotalMonth;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDailyLimit(): float
    {
        return $this->dailyLimit;
    }


    public function setDailyLimit(float $dailyLimit): static
    {
        $this->dailyLimit = $dailyLimit;

        return $this;
    }

    public function getMonthlyLimit(): float
    {
        return $this->monthlyLimit;
    }


    public function setMonthlyLimit(float $monthlyLimit): static
    {
        $this->monthlyLimit = $monthlyLimit;

        return $this;
    }

    public function getDailyTotal(): float
    {
        return $this->dailyTotal;
    }

    public function setDailyTotal(float $dailyTotal): static
    {
        $this->dailyTotal = $dailyTotal;
        return $this;
    }

    public function getMonthlyTotal(): float
    {
        return $this->monthlyTotal;
    }

    public function setMonthlyTotal(float $monthlyTotal): static
    {
        $this->monthlyTotal = $monthlyTotal;
        return $this;
    }

    public function getDailyTotalDate(): ?\DateTimeInterface
    {
        return $this->dailyTotalDate;
    }

    public function setDailyTotalDate(?\DateTimeInterface $dailyTotalDate): static
    {
        $this->dailyTotalDate = $dailyTotalDate;
        return $this;
    }

    public function getMonthlyTotalMonth(): ?string
    {
        return $this->monthlyTotalMonth;
    }

    public function setMonthlyTotalMonth($monthlyTotalMonth): static
    {
        $this->monthlyTotalMonth = $monthlyTotalMonth;
        return $this;
    }

}
