<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\Request\TransactionRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

interface TransactionServiceInterface
{
    public function createTransaction(TransactionRequest $request): JsonResponse;
}