<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        // Создание обычного пользователя
        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,'general_user'));
        $user->setRoles(["ROLE_USER"]);
        $user->setBalance(0);
        $manager->persist($user);

        // Создание супермена
        $user = new User();
        $user->setEmail('admin@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,'super_admin'));
        $user->setRoles(["ROLE_SUPER_ADMIN", "ROLE_USER"]);
        $user->setBalance(0);
        $manager->persist($user);

        $manager->flush();
    }
}