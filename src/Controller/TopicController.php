<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Entity\Alerte;
use App\Form\TopicType;
use App\Entity\Categorie;
use App\Repository\AlerteRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TopicController extends AbstractController
{
    #[Route('/topic/{id}/delete', name: 'delete_topic')]
    public function delete(ManagerRegistry $doctrine, Topic $topic): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $currentUser = $this->getUser();
        
        // Vérifier si l'utilisateur est connecté
        if (!$currentUser) {
            throw new AccessDeniedException('Vous devez être connecté pour supprimer un topic.');
        }
        
        // Vérifier si l'utilisateur actuel est l'auteur du topic
        if ($currentUser !== $topic->getUser()) {
            throw new AccessDeniedException('Vous n\'avez pas le droit de supprimer un topic qui ne vous appartient pas.');
        }
        
        // Récupération de l'Entity Manager de Doctrine pour effectuer des opérations sur la base de données
        $entityManager = $doctrine->getManager();
        
        // Récupérer tous les posts associés à ce topic
        $posts = $topic->getPosts();
        
        // Boucle sur chaque post pour le supprimer
        foreach ($posts as $post) {
            $entityManager->remove($post);
        }
        
        // Suppression du topic lui-même
        $entityManager->remove($topic);
        // Appliquer les modifications (suppressions) à la base de données
        $entityManager->flush();
        
        // Redirection vers la page de la catégorie après la suppression du topic
        return $this->redirectToRoute('app_categorie');
    }
    
}
