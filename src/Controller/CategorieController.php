<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Alerte;
use App\Form\TopicType;
use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategorieController extends AbstractController
{
    /**
     * Propriété pour stocker une instance de ManagerRegistry.
     * ManagerRegistry fournit des méthodes pour accéder aux objets EntityManager et aux repositories.
     */
    private $doctrine;

    /**
     *
     * @param ManagerRegistry $doctrine Une instance de ManagerRegistry, injectée automatiquement par Symfony.
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Méthode pour afficher la liste des catégories.
     * Cette action récupère toutes les catégories triées par nom dans l'ordre ascendant, ainsi que la dernière alerte.
     * Elle passe ensuite ces données à une vue pour les afficher.
     *
     * @return Response Retourne une réponse HTTP contenant le rendu de la vue.
     */
    #[Route('/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        // Récupération de l'EntityManager à partir de la propriété $doctrine.
        $entityManager = $this->doctrine->getManager();

        // Récupération des catégories depuis leur repository. 
        // Les catégories sont triées par nom_categorie dans l'ordre ascendant.
        $categories = $entityManager->getRepository(Categorie::class)->findBy([], ["nom_categorie" => "ASC"]);

        // Récupération de la dernière alerte depuis son repository.
        $latestAlert = $entityManager->getRepository(Alerte::class)->findLatestAlert();
        
        // Rendu de la vue 'categorie/index.html.twig' en passant les catégories et la dernière alerte comme paramètres.
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'latestAlert' => $latestAlert,
        ]);
    }

    #[Route('/categorie/{id}', name: 'show_categorie')]
    #[ParamConverter('categorie', options: ["mapping" => ["id" => "id"]])]

    public function show(int $id, Request $request, TokenStorageInterface $tokenStorage): Response
    {
        // Récupération de l'EntityManager pour effectuer des opérations liées à la base de données.
        $entityManager = $this->doctrine->getManager();
    
        // Récupération de la catégorie spécifiée par l'ID.
        $categorie = $entityManager->getRepository(Categorie::class)->find($id);
    
        // Vérification de l'existence de la catégorie. Si elle n'existe pas, redirection vers la liste des catégories.
        if (!$categorie) {
            return $this->redirectToRoute('app_categorie');
        }
    
        // Récupération des sujets (topics) associés à la catégorie.
        $topics = $categorie->getTopics();
    
        // Création d'une nouvelle instance de Topic et association avec la catégorie courante.
        $topic = new Topic();
        $topic->setCategorie($categorie);
    
        // Création d'un formulaire pour le nouveau sujet.
        $form = $this->createForm(TopicType::class, $topic);
    
        // Gestion de la soumission du formulaire.
        $form->handleRequest($request);
    
        // Vérifie si le formulaire a été soumis et si les données sont valides.
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Vérifie si l'utilisateur est pleinement authentifié.
            if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
                // Si l'utilisateur n'est pas authentifié, une exception est levée et un message d'accès refusé est affiché.
                throw new AccessDeniedException('Accès refusé. Vous devez être connecté.');
            }

            // Récupère l'utilisateur actuellement connecté.
            $user = $tokenStorage->getToken()->getUser();
            
            // Vérifie si l'utilisateur est vérifié.
            if (!$user->IsVerified()) {
                // Si l'utilisateur n'est pas vérifié, une exception est levée et un message d'accès refusé est affiché.
                throw new AccessDeniedException('Accès refusé. Votre compte doit être vérifié.');
            }
            
            // Associe l'utilisateur récupéré au topic en cours.
            $topic->setUser($user);
            
            // Crée un objet DateTime représentant la date et l'heure actuelles et l'associe en tant que date de création au topic.
            $creationDate = new \DateTime();
            $topic->setCreationDate($creationDate);
            
            // Prépare le topic à être sauvegardé dans la base de données.
            $entityManager->persist($topic);
            // Effectue réellement la sauvegarde du topic dans la base de données.
            $entityManager->flush();
        }
    
        // Récupération de l'utilisateur actuellement connecté (s'il existe).
        $user = $this->getUser();
    
        // Récupération de la dernière alerte.
        $latestAlert = $entityManager->getRepository(Alerte::class)->findLatestAlert();
    
        // Renvoi de la vue associée à cette action, avec les données pertinentes en paramètres.
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
            'topics' => $topics,
            'form' => $form->createView(),
            'is_authenticated' => $this->isGranted('IS_AUTHENTICATED_FULLY'),
            'user' => $user,
            'latestAlert' => $latestAlert,
        ]);
    }

    #[Route('/categorie/{id}/delete', name: 'delete_categorie')]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupération de la catégorie spécifiée par l'ID à partir de la base de données.
        $categorie = $entityManager->getRepository(Categorie::class)->find($id);
    
        // Vérification de l'existence de la catégorie.
        // Si la catégorie n'existe pas, redirection vers la liste des catégories.
        if (!$categorie) {
            return $this->redirectToRoute('app_categorie');
        }
    
        // Vérification des droits d'accès : seuls les créateurs de la catégorie peuvent la supprimer.
        // Si l'utilisateur actuellement connecté n'est pas le créateur de la catégorie, on lance une exception d'accès refusé.
        if ($categorie->getUser() !== $this->getUser()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette catégorie.');
        }
    
        // Suppression de la catégorie de la base de données.
        $entityManager->remove($categorie);
        // Exécution des modifications sur la base de données.
        $entityManager->flush();
    
        // Redirection vers la liste des catégories après la suppression.
        return $this->redirectToRoute('app_categorie');
    }

}
