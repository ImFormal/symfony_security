<?php

namespace App\Controller;

use App\Repository\AccountRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly AccountRepository $accountRepository
    ) {}

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    //Méthode qui vérifie l'activation du compte
    #[Route(path: '/verif', name: 'app_security_verif')]
    public function verif(): Response
    {
        //Récupérer l'utilisateur connecté
        $user = $this->getUser();
        //Tester si le compte est activé
        if($user->isStatus()== false) {
            //Redirection vers la route de déconnexion
            return $this->redirectToRoute('app_logout');
        }
        //Redirection vers la page d'accueil
        return $this->redirectToRoute('app_home');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/', name:"app_home")]
    public function home() :Response {
        return new Response("Bienvenue");
    }
}