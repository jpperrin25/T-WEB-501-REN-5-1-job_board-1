<?php


namespace App\Service;


use App\Entity\Company;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SecurityService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private CompanyRepository $companyRepository;
    private EntityManagerInterface $em;
    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        CompanyRepository $companyRepository,
        EntityManagerInterface $em
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->companyRepository = $companyRepository;
        $this->em = $em;
    }

    public function registerUser(array $formData): bool
    {
        try {
            // Check if the user already exists
            if ($this->userRepository->findOneBy(['email' => $formData['email']])) {
                throw new \Exception('User with this email already exists.');
            }

            $user = new User();
            $user->setEmail($formData['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $formData['password']));
            $user->setFirstname($formData['firstname']);
            $user->setLastname($formData['lastname']);
            $user->setPhone($formData['phone'] ?? null);
            $user->setAddress($formData['address'] ?? null);
            $user->setPostalCode($formData['postal_code']);
            $user->setCity($formData['city']);
            $user->setGraduate($formData['graduate'] ?? null);
            $user->setRoles(["ROLE_USER"]);

            $this->userRepository->registerUser($user);
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function registerCompany(array $formData): bool
    {
        try {
            $this->em->wrapInTransaction(function () use ($formData) {
                if ($this->companyRepository->findOneBy(['name' => $formData['name']])) {
                    throw new \Exception('Company with this name already exists.');
                }
                if ($this->userRepository->findOneBy(['email' => $formData['email']])) {
                    throw new \Exception('User with this email already exists.');
                }

                $company = new Company();
                $company->setName($formData['name']);
                $company->setDescription($formData['description'] ?? null);
                $company->setSiret($formData['siret']);

                $this->companyRepository->registerCompany($company);
                $registered_company = $this->companyRepository->findOneBy(['name' => $company->getName()]);

                $user = new User();
                $user->setEmail($formData['email']);
                $user->setPassword($this->passwordHasher->hashPassword($user, $formData['password']));
                $user->setFirstname($formData['firstname']);
                $user->setLastname($formData['lastname']);
                $user->setPhone($formData['phone'] ?? null);
                $user->setAddress($formData['address'] ?? null);
                $user->setPostalCode($formData['postal_code']);
                $user->setCity($formData['city']);
                $user->setGraduate($formData['graduate'] ?? null);
                $user->setRoles(["ROLE_COMPANY"]);
                $user->setCompany($registered_company);

                $this->userRepository->registerUser($user);
            });
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function loginUser(array $formData): ?User
    {
        $user = $this->userRepository->findOneBy(['email' => $formData["email"]]);

        if (!$user) {
            return null;
        }

        if ($this->passwordHasher->isPasswordValid($user, $formData["password"])) {
            return $user;
        }
        return null;
    }

}
