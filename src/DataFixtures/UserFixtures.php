<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ){
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            ['email' => 'user1@example.com', 'name' => 'John Doe', 'phone' => '1234567890', 'dailyLimit' => 1000.99, 'monthlyLimit' => 30000.99, 'password' => 'password1'],
            ['email' => 'user2@example.com', 'name' => 'Jane Smith', 'phone' => '0987654321', 'dailyLimit' => 2000.99, 'monthlyLimit' => 40000.99, 'password' => 'password2'],
            ['email' => 'user3@example.com', 'name' => 'Alice Johnson', 'phone' => '1122334455', 'dailyLimit' => 1500.99, 'monthlyLimit' => 35000.99, 'password' => 'password3'],
        ];

        foreach ($usersData as $userData) {
            $user = new User();
            $user->setEmail($userData['email'])
                ->setName($userData['name'])
                ->setPhone($userData['phone'])
                ->setRoles(['ROLE_USER'])
                ->getLimit()->setDailyLimit($userData['dailyLimit'])
                ->setMonthlyLimit($userData['monthlyLimit']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $userData['password']));

            $manager->persist($user);
        }

        $manager->flush();
    }
}