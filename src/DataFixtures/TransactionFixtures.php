<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TransactionFixtures extends Fixture
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $courseRepository = $manager->getRepository(Course::class);
        $userRepository = $manager->getRepository(User::class);
        // Получаем пользователя
        $user = $userRepository->findOneBy(['email' => 'test@gmail.com']);

        // Получаем существующие курсы
        $rentCourses = $courseRepository->findBy(['type' => 1]);
        $buyCourses = $courseRepository->findBy(['type' => 3]);

        $transactions = [
            // Арендованные курс, у которых закончился срок аренды
            [
                'typeOperation' => 1,
                'value' => $rentCourses[0]->getCost(),
                'periodValidity' => new \DateTime('2021-04-24 00:00:00'),
                'course' => $rentCourses[0],
                'userBilling' => $user,
                'createdAt' => new \DateTime('2021-04-17 00:00:00')
            ],
            [
                'typeOperation' => 1,
                'value' => $rentCourses[1]->getCost(),
                'periodValidity' => new \DateTime('2021-04-24 00:00:00'),
                'course' => $rentCourses[1],
                'userBilling' => $user,
                'createdAt' => new \DateTime('2021-04-17 00:00:00')
            ],
            // Арендованные курс, у которых еще не закончился срок аренды
            [
                'typeOperation' => 1,
                'value' => $rentCourses[2]->getCost(),
                'periodValidity' => (new \DateTime())->modify('+1 day'),
                'course' => $rentCourses[2],
                'userBilling' => $user,
                'createdAt' => (new \DateTime())->modify('-6 day')
            ],
            [
                'typeOperation' => 1,
                'value' => $rentCourses[3]->getCost(),
                'periodValidity' => (new \DateTime())->modify('+6 day'),
                'course' => $rentCourses[3],
                'userBilling' => $user,
                'createdAt' => (new \DateTime())->modify('-1 day')
            ],
            // Купленые курсы
            [
                'typeOperation' => 1,
                'value' => $buyCourses[0]->getCost(),
                'course' => $buyCourses[0],
                'userBilling' => $user,
                'createdAt' => new \DateTime('2021-05-01 00:00:00')
            ],
            [
                'typeOperation' => 1,
                'value' => $buyCourses[1]->getCost(),
                'course' => $buyCourses[1],
                'userBilling' => $user,
                'createdAt' => new \DateTime('2021-05-03 00:00:00')
            ],
        ];

        // Запись объектов
        foreach ($transactions as $transaction) {
            $newTransaction = new Transaction();
            $newTransaction->setTypeOperation($transaction['typeOperation']);
            $newTransaction->setCourse($transaction['course']);
            $newTransaction->setUserBilling($transaction['userBilling']);
            $newTransaction->setCreatedAt($transaction['createdAt']);
            $newTransaction->setValue($transaction['value']);
            if (isset($transaction['periodValidity'])) {
                $newTransaction->setPeriodValidity($transaction['periodValidity']);
            }
            $manager->persist($newTransaction);
        }

        $manager->flush();
    }
}
