<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Limit;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransactionServiceIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testConcurrentTransactionsWithGuzzle(): void
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find(1);
        $this->assertNotNull($user, 'User with ID 1 must exist.');
        $this->entityManager->getRepository(Transaction::class)->deleteAllByUser($user);

        /** @var Limit $limit */
        $limit = $user->getLimit();
        $this->assertNotNull($limit, 'Limit for user with ID 1 must exist.');

        $limit->setUser($user)
            ->setDailyLimit(100)
            ->setDailyTotal(0)
            ->setMonthlyLimit(1000)
            ->setMonthlyTotal(0);

        $this->entityManager->persist($user);
        $this->entityManager->persist($limit);
        $this->entityManager->flush();

        $numTransactions = 5;
        $amountPerTransaction = 30;

        $client = new Client([
            'base_uri' => 'http://127.0.0.1:8000',
            'http_errors' => false,
        ]);

        $promises = [];
        for ($i = 0; $i < $numTransactions; $i++) {
            $promises[] = $client->postAsync('/api/transactions', [
                'json' => [
                    'userId' => $user->getId(),
                    'amount' => $amountPerTransaction,
                ],
            ]);
        }

        $results = Promise\Utils::settle($promises)->wait();

        $successCount = 0;
        $errorCount = 0;
        foreach ($results as $result) {
            if ($result['state'] === 'fulfilled') {
                $response = $result['value'];
                $statusCode = $response->getStatusCode();
                if ($statusCode === 201) {
                    $successCount++;
                } elseif ($statusCode === 400) {
                    $errorCount++;
                    $body = json_decode($response->getBody()->getContents(), true);
                    $this->assertEquals('Daily limit exceeded', $body['error'] ?? '');
                } else {
                    $this->fail('Unexpected status code: ' . $statusCode);
                }
            } else {
                $exception = $result['reason'];
                $this->fail('Request failed: ' . $exception->getMessage());
            }
        }

        $expectedSuccesses = intval(floor($limit->getDailyLimit() / $amountPerTransaction));
        $expectedFailures = $numTransactions - $expectedSuccesses;

        $this->assertEquals(
            $expectedSuccesses,
            $successCount,
            "Expected $expectedSuccesses successful transactions due to daily limit."
        );

        $this->assertEquals(
            $expectedFailures,
            $errorCount,
            "Expected $expectedFailures failed transactions due to daily limit."
        );

        $this->entityManager->clear();

        /** @var Limit $updatedLimit */
        $updatedLimit = $this->entityManager->getRepository(Limit::class)->find($limit->getId());

        $expectedTotal = $expectedSuccesses * $amountPerTransaction;

        $this->assertEquals(
            $expectedTotal,
            $updatedLimit->getDailyTotal(),
            'Daily total should match the sum of successful transactions'
        );
        $this->assertEquals(
            $expectedTotal,
            $updatedLimit->getMonthlyTotal(),
            'Monthly total should match the sum of successful transactions'
        );

        $transactions = $this->entityManager->getRepository(Transaction::class)->findBy(['user' => $user]);
        $this->assertCount(
            $expectedSuccesses,
            $transactions,
            "Should have $expectedSuccesses successful transactions in the database"
        );
    }
}
