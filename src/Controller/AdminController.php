<?php

namespace App\Controller;


use App\Entity\Job;
use App\Form\JobType;
use App\Repository\CompanyRepository;
use App\Repository\JobRepository;
use App\Repository\UserRepository;
use App\Service\SecurityService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route(path: "/admin")]
#[IsGranted('ROLE_ADMIN')]

final class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('dashboard/candidate.html.twig');
    }


    #[Route('/new/user', name: 'app_admin_new_user')]
    public function newUser(Request $request, SecurityService $securityService, LoggerInterface $logger, UserRepository $userRepository): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->render('security/register.html.twig', ['error' => null]);
        }
        if (!$this->isCsrfTokenValid('register', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        try {
            if (empty($request->request->get('name')) && empty($request->request->get('siret'))) {
                $data = $request->request->all();
                foreach ($data as $key => $value) {
                    $data[$key] = htmlspecialchars(trim($value));
                }
                if (!$securityService->registerUser($data)) {
                    $this->addFlash('error', 'Erreur: L\'utilisateur n\'a pas pu être créer.');
                    return $this->redirectToRoute('app_admin_new_user');
                }
                $this->addFlash('success', 'Utilisateur créé avec succes!');
                return $this->redirectToRoute('app_admin_users');
            }
            if (!$securityService->registerCompany($request->request->all())) {
                $this->addFlash('error', 'Erreur: L\'entrprise n\'a pas pu être créer.');
                return $this->redirectToRoute('app_admin_new_user');
            }
            $this->addFlash('success', 'Entreprise créée avec succes!');
            return $this->redirectToRoute('app_admin_users');

        } catch (\Throwable $th) {
            $logger->error('Error during admin user/company creation: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_admin_new_user');
        }
    }

    #[Route(path: '/new/job', name: 'app_admin_new_job', methods: ['GET', 'POST'])]
    public function newJob(Request $request, CompanyRepository $companyRepository, JobRepository $jobRepository, LoggerInterface $logger): Response
    {
        try {
            if ($request->isMethod('GET')) {
                return $this->render('admin/newJob.html.twig', [
                    'companies' => $companyRepository->findAll(),
                ]);
            }
            $company = $companyRepository->find($request->request->get('company_id'));
            if (!$this->isCsrfTokenValid('newJob' . $company->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $job = new Job();
            $form = $this->createForm(JobType::class, $job, ['admin-creation' => $this->isGranted('ROLE_ADMIN')]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $jobRepository->addJob($job);
                $this->addFlash('success', 'Job crée avec succes!');
                return $this->redirectToRoute('app_job_index');
            }
            $this->addFlash('error', "Le job n'a pas pu être créé.");
            return $this->redirectToRoute('app_job_index');
        } catch (\Throwable $th) {
            $logger->error('Error during admin job creation: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_admin_new_job');
        }
    }
}
