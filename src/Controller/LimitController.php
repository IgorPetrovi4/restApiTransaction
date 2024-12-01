<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\Responce\LimitResponse;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class LimitController extends AbstractController
{
    #[Route('/api/{user}/limits', methods: ['GET'])]
    #[OA\Get(
        summary: "Get user limits",
        responses: [
            new OA\Response(
                response: 200,
                description: "Get user limits",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: LimitResponse::class))

                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal Server Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ]
    )]
    #[OA\Parameter(
        name: 'user',
        description: 'The ID of the user',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    public function get(int $user, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($user);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $limit = $user->getLimit();
        $limitDto = new LimitResponse(
            user: $user->getEmail(),
            dailyLimit: $limit->getDailyLimit(),
            dailyTotal: $limit->getDailyTotal(),
            dailyTotalDate: $limit->getDailyTotalDate() ? $limit->getDailyTotalDate()->format('Y-m-d H:i:s') : new \DateTime()->format('Y-m-d H:i:s'),
            monthlyLimit: $limit->getMonthlyLimit(),
            monthlyTotal: $limit->getMonthlyTotal(),
            monthlyTotalMonth: $limit->getMonthlyTotalMonth() ?? new \DateTime()->format('Y-m'),
            message: 'Current user limits'
        );

        return new JsonResponse($limitDto);
    }
}
