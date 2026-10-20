<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\Job;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $entityManager

    ) {
        parent::__construct($registry, Job::class);
    }

    public function addJob(Job $job): void
    {
        $this->entityManager->persist($job);
        $this->entityManager->flush();
    }

    public function removeJob(Job $job): void
    {
        $this->entityManager->remove($job);
        $this->entityManager->flush();
    }

    public function applyJob(Application $application): void
    {
        $this->entityManager->persist($application);
        $this->entityManager->flush();
    }

    public function search(array $filters, array $postalRanges = []): array
    {
        $qb = $this->createQueryBuilder('job')
            ->leftJoin('job.id_company', 'company')
            ->addSelect('company')
            ->orderBy('job.id', 'DESC');

        if ($filters['keyword'] !== '') {
            $qb->andWhere('LOWER(job.title) LIKE :keyword OR LOWER(job.description) LIKE :keyword OR LOWER(company.name) LIKE :keyword')
                ->setParameter('keyword', '%' . mb_strtolower($filters['keyword']) . '%');
        }

        if ($filters['city'] !== '') {
            $condition = 'LOWER(job.city) LIKE :city OR LOWER(job.address) LIKE :city';
            $qb->setParameter('city', '%' . mb_strtolower($filters['city']) . '%');

            if (ctype_digit($filters['city'])) {
                $condition .= ' OR job.postal_code = :postalCode';
                $qb->setParameter('postalCode', (int) $filters['city']);
            }
            $qb->andWhere($condition);
        }

        if ($filters['type'] !== []) {
            $qb->andWhere('LOWER(job.type) IN (:types)')
                ->setParameter(
                    'types',
                    array_map(
                        static fn(string $type): string => mb_strtolower($type),
                        $filters['type']
                    )
                );
        }

        if ($postalRanges !== []) {
            $or = $qb->expr()->orX();
            foreach ($postalRanges as $i => [$min, $max]) {
                $or->add("job.postal_code BETWEEN :min$i AND :max$i");
                $qb->setParameter("min$i", $min)->setParameter("max$i", $max);
            }
            $qb->andWhere($or);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
//     * @return Job[] Returns an array of Job objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('j.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?Job
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
