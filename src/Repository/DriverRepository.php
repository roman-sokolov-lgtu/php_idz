<?php

namespace App\Repository;

use App\Entity\Driver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Driver>
 *
 * @method Driver|null find($id, $lockMode = null, $lockVersion = null)
 * @method Driver|null findOneBy(array $criteria, array $orderBy = null)
 * @method Driver[]    findAll()
 * @method Driver[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DriverRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Driver::class);
    }

    public function save(Driver $driver, bool $flush = false): void
    {
        $this->getEntityManager()->persist($driver);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Driver $driver, bool $flush = false): void
    {
        $this->getEntityManager()->remove($driver);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Driver[] Returns an array of active Driver entities
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.is_active = :val')
            ->setParameter('val', true)
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 