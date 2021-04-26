<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public function deposit(User $user, float $amount): void
    {
        $this->em->getConnection()->beginTransaction();
        try{
            // Создаем запись транзакции с типом deposit
            $transaction = new Transaction();
            $transaction->setUserBilling($user);
            $transaction->setTypeOperation(2);
            $transaction->setCreatedAt(new \DateTime());
            $transaction->setValue($amount);

            // Пополняем счет пользователя
            $user->setBalance($user->getBalance() + $amount);

            // Сохраняем изменения в бд
            $this->em->persist($transaction);
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $exception) {
            // В случае ошибки откатываем изменения
            $this->em->rollBack();
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function payment(User $user, Course $course, float $amount): ?\DateTimeInterface
    {
        $this->em->getConnection()->beginTransaction();
        try{
            if ($user->getBalance() - $course->getCost() >= 0) {
                // Создаем запись транзакции с типом deposit
                $transaction = new Transaction();
                $transaction->setUserBilling($user);
                $transaction->setTypeOperation(1);
                $transaction->setCourse($course);
                $transaction->setCreatedAt(new \DateTime());
                // Проверка типа курса (арендный, полный)
                if ($course->getTypeFormatString() === 'rent') {
                    $time = (new \DateTime())->add(new \DateInterval('P1W'))->setTimezone(new \DateTimeZone('Europe/Moscow'));
                    $transaction->setPeriodValidity($time);
                }
                $transaction->setValue($amount);

                // Пополняем счет пользователя
                $user->setBalance($user->getBalance() - $amount);

                // Сохраняем изменения в бд
                $this->em->persist($transaction);
                $this->em->flush();
                $this->em->getConnection()->commit();

                if ($transaction->getPeriodValidity()) {
                    return $transaction->getPeriodValidity();
                }

                return null;
            }

            throw new \Exception('У вас на счете недостаточно средств.', Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Exception $exception) {
            // В случае ошибки откатываем изменения
            $this->em->getConnection()->rollBack();
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }
    }
}