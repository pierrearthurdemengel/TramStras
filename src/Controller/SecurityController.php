<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, RateLimiterFactory $loginLimiter): Response
    {
        // Création d'une instance de limiteur pour la tentative de connexion.
        $limiter = $loginLimiter->create('login');
    
        // Si le taux limite est dépassé (trop de tentatives de connexion),
        // une exception est levée pour informer l'utilisateur.
        if (false === $limiter->consume()->isAccepted()) {
            throw $this->createAccessDeniedException('Trop de tentatives de connexion. Veuillez réessayer dans 5 minutes.');
        }

        // Récupère l'erreur d'authentification s'il y en a une lors de la dernière tentative.
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupère le dernier nom d'utilisateur entré par l'utilisateur.
        $lastUsername = $authenticationUtils->getLastUsername();

        // Renvoie le template de connexion avec les éventuelles erreurs et le dernier nom d'utilisateur.
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
