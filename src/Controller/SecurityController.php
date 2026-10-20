<?php

namespace App\Controller;

use App\Form\LoginType;
use App\Form\RegisterType;
use App\Service\SecurityService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'secu.login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        return $this->render('security/login.html.twig', ["error" => $error]);
    }



    #[Route(path: '/register', name: 'secu.register', methods: ['GET', 'POST'])]
    public function registerUser(
        SecurityService $securityService,
        LoggerInterface $logger,
        Request $request
    ): Response {
        if (!$request->isMethod('POST')) {
            return $this->render('security/register.html.twig', ['error' => null]);
        }
        if (!$this->isCsrfTokenValid('register', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        try {
            foreach ($request->request->all() as $key => $value) {
                $value = htmlspecialchars(trim($value));
            }
            if (empty($request->request->get('name')) && empty($request->request->get('siret'))) {

                if ($securityService->registerUser($request->request->all())) {
                    return $this->redirectToRoute('secu.login');
                }
                return $this->redirect($this->generateUrl('secu.register'));
            }

            if ($securityService->registerCompany($request->request->all())) {
                return $this->redirectToRoute('secu.login');
            }
            return $this->redirectToRoute('secu.login');
        } catch (\Throwable $th) {
            $logger->error('Error during user registration: ' . $th->getMessage(), ['exception' => $th]);
            return $this->render('security/register.html.twig', ['error' => $th->getMessage()]);
        }
    }



    #[Route(path: '/logout', name: 'secu.logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
