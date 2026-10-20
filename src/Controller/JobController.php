<?php

namespace App\Controller;

use App\Service\JobService;
use Exception;
use App\Entity\Job;
use App\Entity\User;
use App\Form\JobType;
use Psr\Log\LoggerInterface;
use App\Repository\JobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/job')]
final class JobController extends AbstractController
{
    #[Route(name: 'app_job_index', methods: ['GET'])]
    public function index(Request $request, JobService $jobService): Response
    {
        $result = $jobService->search($request->query);
        return $this->render('job/index.html.twig', [
            'jobs' => $result['jobs'],
            'filters' => $result['filters'],
            'availableRegions' => $jobService->getAvailableRegions(),
        ]);
    }

    #[Route('/new', name: 'app_job_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_COMPANY")]
    public function new(Request $request, JobService $jobService, JobRepository $jobRepository, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        try {
            $getUser = $this->getUser()->getUserIdentifier();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $getUser]);

            if (!$user instanceof User || $user->getCompany() === null) {
                throw new Exception('Error Processing Request', 1);
            }

            $job = $jobService->createJob($this->getUser());

            $form = $this->createForm(JobType::class, $job);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $jobRepository->addJob($job);
                $this->addFlash('success', 'Job créer avec succès!');
                return $this->redirectToRoute('app_job_index');
            }

            return $this->render('job/new.html.twig', [
                'job' => $job,
                'form' => $form,
            ]);
        } catch (\Throwable $th) {
            $logger->error('Error during job creation: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_job_index');
        }
    }

    #[Route('/{id}', name: 'app_job_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Job $job, JobRepository $jobRepository, JobService $jobService): Response
    {
        if ($this->isGranted(('ROLE_ADMIN'))) {
            return $this->render('job/show.html.twig', [
                'job' => $job,
                'allJobs' => $jobRepository->findAll(),
                'ownJob' => true
            ]);
        }
        $ownJob = $jobService->checkJobOwner($this->getUser(), $job);

        return $this->render('job/show.html.twig', [
            'job' => $job,
            'allJobs' => $jobRepository->findAll(),
            'ownJob' => $ownJob
        ]);
    }

    #[Route('/{id}/edit', name: 'app_job_edit', requirements: ['id' => '\d+'], methods: ['GET', 'PUT', 'POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_COMPANY")'))]
    public function edit(Request $request, Job $job, JobRepository $jobRepository, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        try {
            $tokenUser = $this->getUser()->getUserIdentifier();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $tokenUser]);
            $form = $this->createForm(JobType::class, $job);
            $form->handleRequest($request);
            if ($this->isGranted('ROLE_ADMIN') || ($job->getCompany() === $user->getCompany())) {
                if ($form->isSubmitted() && $form->isValid()) {
                    $jobRepository->addJob($job);
                    $this->addFlash('success', 'Job modifié avec succès!');
                    return $this->redirectToRoute('app_job_index');
                }
                return $this->render('job/edit.html.twig', [
                    'job' => $job,
                    'form' => $form,
                ]);
            }

            if (!$user instanceof User || ($job->getCompany() !== $user->getCompany())) {
                throw new Exception('A company only can update her jobs.', 1);
            }
            
            return $this->render('job/edit.html.twig', [
                'job' => $job,
                'form' => $form,
            ]);
        } catch (\Throwable $th) {
            dd('' . $th->getMessage());
            $logger->error('Error during job update: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_job_index');
        }
    }

    #[Route('/{id}', name: 'app_job_delete', requirements: ['id' => '\d+'], methods: ['POST', 'DELETE'])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_COMPANY")'))]
    public function delete(Request $request, Job $job, JobService $jobService, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        try {
            $tokenUser = $this->getUser()->getUserIdentifier();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $tokenUser]);
            if (!$this->isCsrfTokenValid('delete' . $job->getId(), $request->getPayload()->getString('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $jobService->deleteJob($user, $job);
            $this->addFlash('success', 'Job supprimé avec succès!');
            return $this->redirectToRoute('app_job_index');
        } catch (\Throwable $th) {
            $logger->error('Error during delete job: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->render('job/show.html.twig', ['job' => $job]);
        }
    }


    #[Route('/{id}/apply', name: 'app_job_apply', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function apply(Request $request, LoggerInterface $logger, JobService $jobService, Job $job): Response
    {
        try {
            if (!$this->isCsrfTokenValid('apply' . $job->getId(), (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            if ($jobService->applyExist($job, $this->getUser())) {
                $this->addFlash('error', 'Vous avez déjà postulé à ce job.');
                return $this->render('job/show.html.twig', [
                    'job' => $job,
                    'ownJob' => false

                ]);
            }
            if ($jobService->jobOwnedByCompany($job)) {
                $jobService->applyJob($this->getUser(), $job);
                $this->addFlash('success', 'Votre démarche à été bien été prise en compte.');
                return $this->render('job/show.html.twig', [
                    'job' => $job,
                    'ownJob' => false
                ]);
            }
            $this->addFlash('error', 'Une erreur est survenue, votre démarche n\'a pas été prise en compte.');
            return $this->render('job/show.html.twig', [
                'job' => $job,
                'ownJob' => false

            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            $logger->error('Error during delete job: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->render('job/show.html.twig', [
                'job' => $job,
                'ownJob' => false
            ]);
        }
    }

    #[Route(path: '/myJobs', name: 'app_job_owned_by_a_comapny', methods: ['GET'])]
    public function getComapnyJob(Request $request, JobService $jobService, LoggerInterface $logger): Response
    {
        $user = $this->getUser();
        try {
            $jobs = $jobService->getCompanyJobs($user);
            return $this->render('job/index.html.twig', ['jobs' => $jobs]);
        } catch (\Throwable $th) {
            $logger->error('Error during delete job: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_job_index');
        }
    }
}
