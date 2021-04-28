<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByFilter($type, $code, $skipExpired, $user): array
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select('t.id,
                c.code,
                t.createdAt as created_at,
                t.periodValidity as skip_expired,
                t.typeOperation as type,
                t.value as amount'
            )
            ->leftJoin('t.course' , 'c');

        if ($user) {
            $queryBuilder
                ->andWhere('t.userBilling = :user')
                ->setParameter('user', $user);
        }

        if ($type) {
            $queryBuilder
                ->andWhere('t.typeOperation = :type')
                ->setParameter('type', $type === 'payment' ? 1 : 2);
        }

        if ($code) {
            $queryBuilder
                ->andWhere('c.code = :code')
                ->setParameter('code', $code);
        }

        if ($skipExpired) {
            $queryBuilder
                ->andWhere('t.periodValidity > :period')
                ->setParameter('period', new \DateTime());
        }

        return $queryBuilder
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    // /**
    //  * @return Transaction[] Returns an array of Transaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
