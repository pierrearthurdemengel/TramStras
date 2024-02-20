<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactMessageType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactMessageController extends AbstractController
{
    private $tokenManager;

    public function __construct(csrfTokenManagerInterface $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    #[Route('/contact_message', name: 'contact_message')]
    public function new(Request $request, ManagerRegistry $doctrine ): Response
    {
        // Récupération du gestionnaire d'entités
        $entityManager = $doctrine->getManager();

        // Instanciation d'un nouvel objet ContactMessage.
        $contactMessage = new ContactMessage();

        // Création du formulaire associé à l'entité ContactMessage.
        $form = $this->createForm(ContactMessageType::class, $contactMessage);

        // Traitement et vérification de la soumission du formulaire.
        $form->handleRequest($request);
        
        // débogage.
        dump($form->getErrors(true, true));

        // Si le formulaire est soumis et valide...
        if ($form->isSubmitted() && $form->isValid()) {
            
            //bloque la double soumition du post
            $this->csrfTokenManager->refreshToken("form_intention");

            $entityManager->persist($contactMessage);
            $entityManager->flush();

            // Redirection vers la page d'accueil après l'enregistrement réussi du message de contact.
            return $this->redirectToRoute('home');
        }

        // Rendu de la vue associée au formulaire de création d'un message de contact.
        return $this->render('contact_message/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
