<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Company;
use App\Entity\User;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Service\CompanyService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    #[Route('/', name: 'app_company_index', methods: ['GET'])]
    public function getAllCompany(CompanyRepository $companyRepository): Response
    {
        return $this->render('company/index.html.twig', [
            'companies' => $companyRepository->findAll(),
        ]);
    }
    #[Route('/dashboard', name: 'app_company_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_COMPANY')]
    public function dashboard(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('dashboard/company.html.twig');
    }

    #[Route('/{id}', name: 'app_company_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Company $company, CompanyService $companyService, LoggerInterface $logger): Response
    {
        try {
            if (!$this->getUser()) {
                return $this->render('company/show.html.twig', [
                    'company' => $company,
                    'myCompany' => false,
                ]);
            }
            $myCompany = $companyService->checkOwnerCompany($this->getUser(), $company);
            return $this->render('company/show.html.twig', [
                'company' => $company,
                'myCompany' => $myCompany,
            ]);
        } catch (\Throwable $th) {
            dd('' . $th->getMessage());
            $logger->error('Error during show company: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_company_index');
        }
    }

    #[Route('/{id}/jobs', name: 'app_company_jobs', methods: ['GET'])]
    public function index(Company $company): Response
    {
        return $this->render('company/jobs.html.twig', [
            'company' => $company,
            'jobs' => $company->getJobs(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_company_udpate', methods: ['GET', 'PUT', 'POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_COMPANY")'))]
    public function updateCompany(Request $request, Company $company, CompanyService $companyService, CompanyRepository $companyRepository, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        try {
            $user = $companyService->recoverUserAssociate($company);
            $myCompany = $companyService->checkOwnerCompany($this->getUser(), $company);
            if ($request->isMethod('GET')) {
                $company->getId();
                $form = $this->createForm(CompanyType::class, $company);
                $form->handleRequest($request);
                return $this->render('company/edit.html.twig', [
                    'company' => $company,
                    'user' => $user,
                    'form' => $form
                ]);
            }
            $form = $this->createForm(CompanyType::class, $company);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                if ($this->isGranted('ROLE_ADMIN')) {
                    $this->addFlash('success', 'Entreprise modifiée avec succès!');
                    return $this->redirectToRoute('app_company_index');
                }
                $this->addFlash('success', 'Entreprise modifiée!');
                return $this->render('company/index.html.twig', [
                    'companies' => $companyRepository->findAll(),
                    'user' => $user,
                    'form' => $form
                ]);
            }
            return $this->render('company/index.html.twig', [
                'company' => $company,
                'user' => $user,
                'form' => $form
            ]);
        } catch (\Throwable $th) {
            $logger->error('Delete company admin route: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->render('company/show.html.twig', [
                'company' => $company,
                'myCompany' => $myCompany
            ]);
        }

    }

    #[Route('/{id}/delete', name: 'app_admin_company_delete', methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_COMPANY")'))]
    public function deleteCompany(Company $company, Request $request, CompanyService $companyService, CompanyRepository $companyRepository, LoggerInterface $logger): Response
    {
        try {
            if (!$this->isCsrfTokenValid('delete' . $company->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            if ($this->isGranted('ROLE_ADMIN')) {
                $companyService->deleteCompany($company, null);
                $this->addFlash('success', 'Entrepise supprimée!');
                return $this->redirectToRoute('app_company_index');
            }
            $user = $this->getUser();
            $companyService->deleteCompany($company, $user);
            $this->addFlash('success', 'Entrepise supprimée!');
            return $this->redirectToRoute('app_company_index');

        } catch (\Throwable $th) {
            $logger->error('Delete comany admin route: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->render('company/index.html.twig', [
                'error' => 'Error: ' . $th->getMessage(),
                'companies' => $companyRepository->findAll()
            ]);
        }
    }

    #[Route('/candidate/{id}', name: 'app_company_show_candidate', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_COMPANY")'))]
    public function showCandidate(Request $request, CompanyService $companyService, User $user, LoggerInterface $logger): Response
    {
        try {
            $application = $companyService->findApplication($request->query->get('application'));
            return $this->render('user/show.html.twig', ['user' => $user, 'application' => $application]);
        } catch (\Throwable $th) {
            dd('' . $th->getMessage(), ['' => $th]);
            $logger->error('Error on show candidate: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_user_applications');
        }
    }
    // Remove Application of candidate
    #[Route('/application/{id}', name: 'app_company_delete_application', methods: ['POST', 'DELETE'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_COMPANY")'))]
    public function removeCandidate(Request $request, Application $application, CompanyService $companyService, LoggerInterface $logger): Response
    {
        try {
            if (!$this->isCsrfTokenValid('delete' . $application->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }
            $companyService->removeCandidate($application);
            $this->addFlash('success', 'Candidat supprimé!');
            return $this->redirectToRoute('app_user_applications');
        } catch (\Throwable $th) {
            $logger->error('Error on delete application candidate: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_user_applications');
        }
    }
}
