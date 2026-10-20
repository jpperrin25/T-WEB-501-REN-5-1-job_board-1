<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ApplicationRepository;
use App\Repository\JobRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route('/all', name: 'app_admin_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAllUsers(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/dashboard', name: 'app_user_dashboard', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER") or is_granted("ROLE_COMPANY") or is_granted("ROLE_ADMIN")'))]
    public function dashboard(): Response
    {
        if ($this->isGranted('ROLE_COMPANY')) {
            return $this->render('dashboard/company.html.twig', [
                'user' => $this->getUser(),
            ]);
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('dashboard/admin.html.twig', [
                'user' => $this->getUser(),
            ]);
        }
        return $this->render('dashboard/candidate.html.twig', [
            'user' => $this->getUser(),
        ]);

    }

    #[Route('/{id}/edit', name: 'app_user_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST', 'PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        $currentUser = $this->getUser();

        if (
            !$currentUser instanceof User
            || (
                !$this->isGranted('ROLE_ADMIN')
                && $user->getId() !== $currentUser->getId()
            )
        ) {
            throw $this->createAccessDeniedException(
                'Vous ne pouvez pas modifier le profil de cet utilisateur.'
            );
        }

        try {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                if ($this->isGranted('ROLE_ADMIN')) {
                    $this->addFlash('success', 'Utilisateur modifié avec succès!');
                    return $this->redirectToRoute('app_admin_users');
                }
                $this->addFlash('success', 'Profil modifié avec succès!');
                return $this->redirectToRoute('app_user_dashboard');
            }
            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'form' => $form,
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            $logger->error('Error during user update: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_job_index');
        }
    }

    #[Route('/{id}/delete', name: 'app_user_delete', requirements: ['id' => '\d+'], methods: ['POST', 'DELETE'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER") or is_granted("ROLE_COMPANY") or is_granted("ROLE_ADMIN")'))]
    public function delete(
        Request $request,
        User $user,
        UserRepository $userRepository,
        Security $security,
        UserService $userService,
        LoggerInterface $logger
    ): Response {
        $currentUser = $this->getUser();

        if (
            !$currentUser instanceof User
            || (
                !$this->isGranted('ROLE_ADMIN')
                && $user->getId() !== $currentUser->getId()
            )
        ) {
            throw $this->createAccessDeniedException(
                'Vous ne pouvez pas supprimer le compte de cet utilisateur.'
            );
        }

        if (
            !$this->isCsrfTokenValid(
                'delete' . $user->getId(),
                $request->request->getString('_token')
            )
        ) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        $isSelfDelete = $user->getId() === $currentUser->getId();
        try {

            if ($isSelfDelete) {
                $userService->deleteUser($user, $currentUser);
                
                $response = $security->logout(false);
                
                $this->addFlash('success', 'Votre compte a été supprimé.');
                return $response ?? $this->redirectToRoute('app_home');
            }
            $userRepository->deleteUser($user);
            $this->addFlash('success', 'Utilisateur supprimé !');

            return $this->redirectToRoute('app_admin_users');
        } catch (\Throwable $th) {
            dd('' . $th->getMessage());
            $logger->error('Error during user delete: ' . $th->getMessage(), ['exception' => $th]);
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('app_job_index');
        }
    }

    #[Route('/applications', name: 'app_user_applications', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getApplication(UserRepository $userRepository, JobRepository $jobRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            // dd($jobRepository->findAll());
            return $this->render('admin/applications.html.twig', [
                'jobs' => $jobRepository->findAll()
            ]);
        }
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $user = $userRepository->findOneBy([
            'email' => $currentUser->getUserIdentifier(),
        ]);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        if ($user->getCompany() !== null) {
            return $this->render('company/applications.html.twig', [
                'jobs' => $user->getCompany()->getJobs(),
            ]);
        }

        return $this->render('user/applications.html.twig', [
            'applications' => $user->getApplications(),
        ]);
    }
}
