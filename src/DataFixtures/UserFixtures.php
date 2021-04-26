<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\PaymentService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    private $paymentService;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, PaymentService $paymentService)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->paymentService = $paymentService;
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function load(ObjectManager $manager): void
    {
        // Создание обычного пользователя
        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,'general_user'));
        $user->setBalance(0);
        $user->setRoles(["ROLE_USER"]);
        $manager->persist($user);

        // Создание супермена
        $admin = new User();
        $admin->setEmail('admin@gmail.com');
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,'super_admin'));
        $admin->setRoles(["ROLE_SUPER_ADMIN", "ROLE_USER"]);
        $admin->setBalance(0);
        $manager->persist($admin);
        $manager->flush();

        // Получаем сумму первоначального поплнения
        $amount = $_ENV['DEPOSIT_AMOUNT'];

        // Пополнение баланса
        $this->paymentService->deposit($user, $amount);
        $this->paymentService->deposit($admin, $amount);
    }
}