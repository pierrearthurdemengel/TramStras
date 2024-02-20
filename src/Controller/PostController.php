<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class PostController extends AbstractController
{
    private $tokenManager;

    public function __construct(csrfTokenManagerInterface $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    #[Route('/post', name: 'app_post')]
    public function index(ManagerRegistry $doctrine): Response
    {
        // Récupération de tous les posts depuis la base de données.
        $posts = $doctrine->getRepository(Post::class)->findAll();

        // Renvoie la vue "index.html.twig" du dossier "post", en passant la liste des posts comme données.
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/topic/{id}', name: 'app_topic_show')]
    public function show(Request $request, Topic $topic = null, ManagerRegistry $doctrine): Response
    {
        
        // Si le topic n'est pas trouvé, redirection vers la page des catégories.
        if (!$topic) {
            return $this->redirectToRoute('app_categorie');
        }

        $categorie = $topic->getCategorie();
    
        // Récupération des posts associés au topic depuis la base de données.
        $posts = $doctrine->getRepository(Post::class)->findBy(['topic' => $topic]);
    
        // Initialisation d'une nouvelle instance de Post pour le formulaire d'ajout.
        $post = new Post();
        
        // Création du formulaire associé au type "PostType" et à l'instance de Post.
        $form = $this->createForm(PostType::class, $post);
    
        // Traitement du formulaire : liaison des données envoyées avec l'entité Post.
        $form->handleRequest($request);

        // Si le formulaire est soumis et ses données sont valides :
        if ($form->isSubmitted() && $form->isValid()) {
            //bloque la double soumition du post
            $this->csrfTokenManager->refreshToken("create_post");

            // Associe le post au topic courant et à l'utilisateur connecté, puis défini la date actuelle comme date de publication.
            $post->setTopic($topic);
            $post->setUser($this->getUser());
            $post->setDatePost(new \DateTime());
    
            // Persister (préparer à sauvegarder) le post dans la base de données.
            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
    
            // Rediriger vers la page du même topic, pour afficher le post nouvellement ajouté.
            return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
        } 
    
        // Renvoie la vue "show.html.twig" du dossier "topic", en passant le topic, ses posts et le formulaire d'ajout de post comme données.
        return $this->render('topic/show.html.twig', [

            'categorie' => $categorie,
            'topic' => $topic,
            'posts' => $posts, 
            'form' => $form->createView(),
        ]);
    }
    
    


    #[Route('/post/create/{id}', name: 'add_post')]
    public function add(Request $request, $id): Response
    {
        // Récupération du topic correspondant à l'ID fourni depuis la base de données.
        $topic = $this->getDoctrine()->getRepository(Topic::class)->find($id);
        
        // Si le topic n'existe pas en base de données (l'objet $topic est null), 
        // une exception "NotFoundException" sera levée avec un message d'erreur.
        if (!$topic) {
            throw $this->createNotFoundException('Le topic n\'existe pas.');
        }
        
        // Création d'une nouvelle instance de l'entité Post.
        $post = new Post();
    
        // Initialisation du formulaire associé à l'entité Post.
        // "PostType" est un formulaire spécifiquement défini pour manipuler les instances de la classe Post.
        $form = $this->createForm(PostType::class, $post);
        
        // La méthode "handleRequest" relie les données du formulaire HTTP (contenues dans $request) à l'instance $post.
        $form->handleRequest($request);
        
        // Si le formulaire a été soumis et que toutes les données sont valides (en fonction des contraintes définies dans PostType) :
        if ($form->isSubmitted() && $form->isValid()) {
            // Liaison du post au topic récupéré précédemment.
            $post->setTopic($topic);
            
            // Liaison du post à l'utilisateur actuellement connecté.
            $post->setUser($this->getUser());
            
            // Définition de la date actuelle comme date de publication du post.
            $post->setDatePost(new \DateTime());
        
            // Récupération de l'EntityManager de Doctrine, il est responsable de la persistance des entités.
            $entityManager = $this->getDoctrine()->getManager();
    
            // Indication à l'EntityManager que l'entité $post doit être sauvegardée en base de données.
            $entityManager->persist($post);
    
            // Exécution effective de l'opération en base de données (ici, insertion du nouveau post).
            $entityManager->flush();
        
            // Après l'ajout du post, redirection vers la page d'affichage du topic associé pour voir le post nouvellement créé.
            return $this->redirectToRoute('app_topic_show', ['id' => $topic->getId()]);
        }
        
        // Si le formulaire n'est pas soumis ou contient des erreurs, affichage du formulaire à l'utilisateur.
        return $this->render('post/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}/delete', name: 'delete_post')]
    public function deletePost(EntityManagerInterface $entityManager, Post $post): Response
    {
        // Récupération de l'utilisateur actuellement connecté. 
        // Retourne null si aucun utilisateur n'est authentifié.
        $currentUser = $this->getUser();
    
        // Si aucun utilisateur n'est connecté, on lève une exception pour indiquer que l'accès est refusé.
        if (!$currentUser) {
            throw new AccessDeniedException('Vous devez être connecté.');
        }
    
        // Vérification des droits de suppression :
        // L'utilisateur doit être l'auteur du post ou avoir le rôle administrateur ('ROLE_ADMIN').
        // Si ces conditions ne sont pas remplies, on lève une exception pour indiquer que l'accès est refusé.
        if ($post->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce post.');
        }
        
        // Suppression du post à l'aide de l'EntityManager.
        $entityManager->remove($post);
    
        // Validation de la suppression et exécution de la requête en base de données.
        $entityManager->flush();
        
        // Après suppression, redirection de l'utilisateur vers la page d'affichage du topic associé.
        // Cela permet de confirmer visuellement que le post a bien été supprimé.
        return $this->redirectToRoute('app_topic_show', ['id' => $post->getTopic()->getId()]);
    }
}
