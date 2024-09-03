<?php

namespace App\Repository;

use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function findAllByUserId(int $userId)
    {
        return $this->createQueryBuilder('e')
            ->select('e.id,e.amount,e.expenseDate as expenseDate, e.description,e.createdAt, e.updatedAt', 'c.categoryName')
            ->join('App\Entity\Category', 'c', 'WITH', 'e.categoryId = c.id')
            ->where('e.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findTotalExpensesPerCategory(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('e')
            ->select('c.categoryName as category, SUM(e.amount) as total')
            ->leftJoin('App\Entity\Category', 'c', 'WITH', 'e.categoryId = c.id')
            ->where('e.expenseDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('c.categoryName')
            ->getQuery()
            ->getArrayResult();
    }
    

    public function findTotalExpenses(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.expenseDate, SUM(e.amount) as totalAmount')
            ->where('e.expenseDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('e.expenseDate')
            ->getQuery()
            ->getArrayResult(); 
    }
    

    public function findTotalExpensesByUser(int $userId): array
    {
        return $this->createQueryBuilder('e')
            ->select('c.id, c.categoryName as category, SUM(e.amount) as totalAmount')
            ->leftJoin('App\Entity\Category', 'c', 'WITH', 'e.categoryId = c.id')
            ->where('e.userId = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('c.id, c.categoryName')
            ->getQuery()
            ->getArrayResult();
    }
}
