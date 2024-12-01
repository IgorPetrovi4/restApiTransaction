<?php
declare(strict_types=1);

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionRequest
{
    public function __construct(

        #[Assert\NotNull]
        #[Assert\Type('integer')]
        public ?int   $userId = 1,

        #[Assert\NotNull]
        #[Assert\Type('float')]
        public ?float $amount = 0.00,
    ){
    }

}