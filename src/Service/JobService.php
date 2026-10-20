<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Company;
use App\Entity\Job;
use App\Entity\User;
use App\Repository\JobRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


final class JobService
{

    private const REGIONS = [
        'Bretagne' => [[22000, 22999], [29000, 29999], [35000, 35999], [56000, 56999]],
        'Île-de-France' => [[75000, 75999], [77000, 77999], [78000, 78999], [91000, 91999], [92000, 92999], [93000, 93999], [94000, 94999], [95000, 95999]],
        'Pays de la Loire' => [[44000, 44999], [49000, 49999], [53000, 53999], [72000, 72999], [85000, 85999]],
    ];


    public function __construct(
        private EntityManagerInterface $entityManager,
        private JobRepository $jobRepository
    ) {
    }


    public function createJob(User $user): Job
    {
        $company = $user->getCompany();

        if ($company === null && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new AccessDeniedException('Only a company or an admin can create jobs.');
        }

        $job = new Job();
        if ($company !== null) {
            $job->setCompany($company);
        }
        return $job;
    }

    public function checkJobOwner(?User $user, Job $job): bool
    {
        if (!$user) {
            return false;
        }

        $company = $user->getCompany();
        if (!$company) {
            return false;
        }

        if ($company->getId() !== $job->getCompany()->getId()) {
            return false;
        }
        return true;

    }
    public function deleteJob(User $user, Job $job): void
    {
        $company = $user->getCompany();

        if ($company === null && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new AccessDeniedException('Only a company can delete a job.');
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->jobRepository->removeJob($job);
            return;
        }
        if ($job->getCompany()->getId() !== $company->getId()) {
            throw new Exception('You only can delete your own company\'s jobs', 1);
        }

        foreach ($company->getJobs() as $currentJob) {
            if ($currentJob->getId() === $job->getId()) {
                $this->jobRepository->removeJob($job);
                return;
            }
        }
        throw new AccessDeniedException('A company only can delete her own jobs.');
    }

    public function applyExist(Job $job, User $user): bool
    {
        $applications = $job->getApplications();
        foreach ($applications as $application) {
            if ($application->getUser()?->getId() === $user->getId()) {
                return true;
            }
        }
        return false;
    }
    public function jobOwnedByCompany(Job $currentJob): bool
    {
        $owned = false;
        foreach ($currentJob->getCompany()->getJobs()->getValues() as $job) {
            if ($job->getId() == $currentJob->getId()) {
                $owned = true;
            }
        }
        return $owned;

    }

    public function applyJob(User $user, Job $job): void
    {
        $application = new Application();

        $application->setCreatedAt(new DateTimeImmutable());
        $application->setUser($user);
        $application->setJob($job);
        $application->setCompany($job->getCompany());
        try {
            $this->jobRepository->applyJob($application);
            $user->addApplication($application);
        } catch (\Throwable $th) {
            throw new Exception("Error during apply job", 1);
        }
        return;
    }
    public function search(InputBag $query): array
    {
        // dd($query);
        $filters = [
            'keyword' => trim((string) $query->get('keyword')),
            'city' => trim((string) $query->get('city')),
            'type' => array_values(array_filter(
                $query->all('type'),
                'is_string'
            )),
            'regions' => array_values(array_intersect(
                array_keys(self::REGIONS),
                $query->all('region')
            )),
        ];

        // Traduction des régions en plages de codes postaux
        $ranges = [];
        foreach ($filters['regions'] as $region) {
            array_push($ranges, ...self::REGIONS[$region]);
        }

        return [
            'jobs' => $this->jobRepository->search($filters, $ranges),
            'filters' => $filters,
        ];
    }

    public function getAvailableRegions(): array
    {
        return array_keys(self::REGIONS);
    }

    public function getCompanyJobs(User $user): array
    {
        $company = $user->getCompany();
        if ($company === null) {
            throw new AccessDeniedException('You must to be a company.');
        }
        return $company?->getJobs()?->getValues();
    }
}
