<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\Request\TransactionRequest;
use App\Dto\Responce\LimitResponse;
use App\Entity\Transaction;
use App\Enum\TransactionStatus;
use App\Repository\LimitRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LimitRepository        $limitRepository,
        private UserRepository         $userRepository
    ){
    }

    public function createTransaction(TransactionRequest $request): JsonResponse
    {
        if ($request->userId <= 0) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $user = $this->userRepository->find($request->userId);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
        $connection = $this->entityManager->getConnection();

        try {
            $connection->beginTransaction();

            $limitId = $user->getLimit()->getId();
            $limit = $this->limitRepository->find($limitId, LockMode::PESSIMISTIC_WRITE);

            if (!$limit) {
                $connection->rollBack();
                return new JsonResponse(['error' => 'Limit not found'], 404);
            }

            $now = new \DateTime('UTC');
            $currentMonth = $now->format('Y-m');

            // Reset daily limit if date has changed
            if ($limit->getDailyTotalDate()?->format('Y-m-d') !== $now->format('Y-m-d')) {
                $limit->setDailyTotal(0);
                $limit->setDailyTotalDate($now);
            }

            // Reset monthly limit if month has changed
            if ($limit->getMonthlyTotalMonth() !== $currentMonth) {
                $limit->setMonthlyTotal(0);
                $limit->setMonthlyTotalMonth($currentMonth);
            }

            $amount = $request->amount;

            if (($limit->getDailyTotal() + $amount) > $limit->getDailyLimit()) {
                return new JsonResponse(['error' => 'Daily limit exceeded'], 400);
            }

            if (($limit->getMonthlyTotal() + $amount) > $limit->getMonthlyLimit()) {
                return new JsonResponse(['error' => 'Monthly limit exceeded'], 400);
            }

            $newDailyTotal = $limit->getDailyTotal() + $amount;
            $newMonthlyTotal = $limit->getMonthlyTotal() + $amount;

            $limit->setDailyTotal($newDailyTotal);
            $limit->setMonthlyTotal($newMonthlyTotal);

            $transaction = new Transaction();
            $transaction->setUser($user)
                ->setAmount($amount)
                ->setStatus(TransactionStatus::SUCCESS);

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();
            $connection->commit();

            $limitDto = new LimitResponse(
                user: $user->getEmail(),
                dailyLimit: $limit->getDailyLimit(),
                dailyTotal: $limit->getDailyTotal(),
                dailyTotalDate: $limit->getDailyTotalDate() ? $limit->getDailyTotalDate()->format('Y-m-d H:i:s') : (new \DateTime())->format('Y-m-d H:i:s'),
                monthlyLimit: $limit->getMonthlyLimit(),
                monthlyTotal: $limit->getMonthlyTotal(),
                monthlyTotalMonth: $limit->getMonthlyTotalMonth() ?? (new \DateTime())->format('Y-m'),
                message: 'Transaction status: ' . TransactionStatus::SUCCESS->label()
            );

            return new JsonResponse($limitDto, 201);

        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            return new JsonResponse(['error' => $e->getMessage(), 'Transaction status' => TransactionStatus::FAILED->label()], 500);
        }
    }
}