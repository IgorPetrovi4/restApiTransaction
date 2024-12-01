<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\TransactionRequest;
use App\Dto\Responce\LimitResponse;
use App\Service\TransactionServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly TransactionServiceInterface $transactionService
    ){
    }

    #[Route('/api/transactions', methods: ['POST'])]
    #[OA\Post(
        summary: "Create new user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: TransactionRequest::class)
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Returns the current user limits',
                content: new OA\JsonContent(ref: new Model(type: LimitResponse::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Data validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string'))
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    public function create(#[MapRequestPayload]  TransactionRequest $request): JsonResponse
    {
        return $this->transactionService->createTransaction($request);
    }
}
