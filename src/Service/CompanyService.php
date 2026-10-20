<?php


namespace App\Service;

use App\Entity\Application;
use App\Entity\Company;
use App\Entity\User;
use App\Repository\ApplicationRepository;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CompanyService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private CompanyRepository $companyRepository,
        private ApplicationRepository $applicationRepository
    ) {
    }

    public function checkOwnerCompany(User $user, Company $company): bool
    {
        if ($company->getId() === $user->getId()) {
            return true;
        }
        return false;
    }

    public function findApplication(int $id): ?Application
    {
        $application = $this->applicationRepository->find($id);
        return $application;
    }
    public function deleteCompany(Company $company, User|null $user): void
    {
        if (!$company instanceof Company) {
            throw new Exception("Data invalid: une instance de Company est attendu", 1);
        }
        if ($user === null) {
            $user = $this->userRepository->findOneBy(['company' => $company]);
            $this->companyRepository->removeCompany($company, $user);
            return;
        }
        if ($user->getCompany()->getId() !== $company->getId()) {
            throw new Exception('You cannot delete this company.', 1);
        }
        $this->companyRepository->removeCompany($company, $user);
        return;

    }

    public function removeCandidate(Application $application): void
    {
        if (!$application instanceof Application) {
            throw new Exception('Object of Application class expected', 1);
        }
        $this->companyRepository->removeCandidate($application);
        return;
    }

    public function recoverUserAssociate(Company $company): ?User
    {
        if (!$company instanceof Company) {
            throw new Exception('Object of Company class expected', 1);
        }
        $user = $this->userRepository->findOneBy(['company' => $company->getId()]);
        if (!$user) {
            throw new Exception("User not found", 1);
        }
        return $user;
    }

}