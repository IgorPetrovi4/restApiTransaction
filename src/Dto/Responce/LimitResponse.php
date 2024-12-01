<?php
declare(strict_types=1);

namespace App\Dto\Responce;

use Symfony\Component\Validator\Constraints as Assert;

class LimitResponse
{
    public function __construct(

        #[Assert\NotNull]
        #[Assert\Type('string')]
        public string $user,

        #[Assert\NotNull]
        #[Assert\Type('float')]
        public float  $dailyLimit,

        #[Assert\NotNull]
        #[Assert\Type('float')]
        public float  $dailyTotal,

        #[Assert\NotNull]
        #[Assert\DateTime]
        public string $dailyTotalDate,

        #[Assert\NotNull]
        #[Assert\Type('float')]
        public float  $monthlyLimit,

        #[Assert\NotNull]
        #[Assert\Type('float')]
        public float  $monthlyTotal,

        #[Assert\NotNull]
        #[Assert\Type('string')]
        public string $monthlyTotalMonth,

        #[Assert\NotNull]
        #[Assert\Type('string')]
        public string $message
    ){
    }
}