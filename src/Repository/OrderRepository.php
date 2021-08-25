<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Order::class);
    }

    public function getSellTotals(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.type = :type')
            ->setParameter('type', Order::TYPE_SELL)
            ->select('SUM(o.price * o.quantity) AS priceTotal, SUM(o.quantity) AS quantityTotal')
            ->getQuery()
            ->getScalarResult();
    }

    public function getNextBuyOrder(int $previousBuyOrderId): ?Order
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->andWhere('o.id > :previousId')
            ->setParameter('previousId', $previousBuyOrderId)
            ->andWhere('o.type = :type')
            ->setParameter('type', Order::TYPE_BUY)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}