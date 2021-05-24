<?php

namespace App\Repository;

use App\Entity\ArticlePriceEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArticlePriceEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticlePriceEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticlePriceEntry[]    findAll()
 * @method ArticlePriceEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticlePriceEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticlePriceEntry::class);
    }

    // /**
    //  * @return ArticlePriceEntry[] Returns an array of ArticlePriceEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ArticlePriceEntry
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
