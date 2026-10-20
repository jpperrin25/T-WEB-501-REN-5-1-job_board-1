<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Company::class);
    }

    public function registerCompany(Company $company): void
    {
        $this->entityManager->persist($company);
        $this->entityManager->flush();
        return;
    }

    public function removeCompany(Company $company, User $user): void
    {
        $this->entityManager->remove($company);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return;
    }
    
    public function removeCandidate(Application $application): void
    {
        $this->entityManager->remove($application);
        $this->entityManager->flush();
        return;

    }
    //    /**
//     * @return Company[] Returns an array of Company objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?Company
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }







    /**
     * Récupère les entreprises triées par nom
     * avec leurs offres chargées dans la même requête.
     *
     * @return Company[]
     */
    // public function findAllOrderedByNameWithJobs(): array
    // {
    //     return $this->createQueryBuilder('c')
    //         ->leftJoin('c.jobs', 'j')
    //         ->addSelect('j')
    //         ->orderBy('c.name', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
}
