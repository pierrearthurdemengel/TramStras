<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Repository\AlerteRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlerteController extends AbstractController
{
    /**
     * Ce contrôleur définit des méthodes pour gérer les routes associées aux alertes 
     * et à la récupération d'informations associées, comme les catégories, en utilisant Symfony.
     */

    // Définition d'une route
    #[Route('/', name: 'index')]
    public function index(AlerteRepository $alertRepository): Response
    {
        // Récupère la dernière alerte (basée sur l'ID le plus élevé) à partir du repository d'alerte.
        $latestAlert = $alertRepository->findOneBy([], ['id' => 'DESC']);
        
        // Renvoie une réponse en rendant la vue 'base.html.twig' et en y passant la dernière alerte.
        return $this->render('base.html.twig', [
            'latestAlert' => $latestAlert
        ]);
    }

    // Définition d'une autre route '/alerte' qui ne répond qu'aux requêtes HTTP GET.
    #[Route('/alerte', name: 'get_alerte', methods: ["GET"])]
    public function getAlerte(CategorieRepository $categorieRepository, AlerteRepository $alertRepository): Response
    {
        // Récupère toutes les catégories à partir du repository de catégorie.
        $categories = $categorieRepository->findAll();

        // Encore une fois, récupère la dernière alerte (basée sur l'ID le plus élevé) à partir du repository d'alerte.
        $latestAlert = $alertRepository->findOneBy([], ['id' => 'DESC']);
        
        // Renvoie une réponse en rendant la vue 'alerte/index.html.twig' et en y passant les catégories ainsi que la dernière alerte.
        return $this->render('alerte/index.html.twig', [
            'categories' => $categories,
            'latestAlert' => $latestAlert
        ]);
    }


    // Définit une route pour le chemin '/alerte', accessible uniquement via la méthode HTTP POST.
    #[Route('/alerte', name: 'alerte', methods: ["POST"])]
    public function sendAlerte(
        // Injection de dépendances : ces services sont automatiquement fournis par Symfony.
        Request $request,                     // Fournit des informations sur la requête HTTP entrante.
        EntityManagerInterface $entityManager, // Interface pour interagir avec la base de données via Doctrine.
        Security $security                    // Service pour gérer la sécurité et l'authentification.
    ): Response {
        // Récupère toutes les données de la requête (souvent provenant d'un formulaire).
        $data = $request->request->all();

        // Récupère l'utilisateur actuellement connecté.
        $user = $security->getUser();

        // Crée une nouvelle instance de l'entité Alerte.
        $alerte = new Alerte();

        // Définit les valeurs de cette entité à partir des données de la requête.
        $alerte->setLigne($data['ligne']);
        $alerte->setAlerteDate(new \DateTime()); // Définit la date actuelle pour l'alerte.
        $alerte->setSens($data['sens']);

        // Attribue l'utilisateur connecté comme auteur de l'alerte.
        // Notons qu'on utilise "$this->getUser()" qui est un raccourci pour "$security->getUser()".
        $alerte->setUser($this->getUser());

        // Indique à Doctrine qu'il faut préparer cette entité pour être persistée (sauvegardée) dans la base de données.
        $entityManager->persist($alerte);

        // Exécute la requête SQL qui va effectivement sauvegarder l'entité dans la base de données.
        $entityManager->flush();

        // Redirige l'utilisateur vers une autre route (nommée 'confirmation') 
        // et lui transmet la dernière alerte créée en tant que paramètre.
        return $this->redirectToRoute('confirmation', [
            'latestAlert' => $alerte
        ]);
    }


    #[Route('/confirmation', name: 'confirmation', methods: ["GET"])]
    public function confirmation(
        // Injection de dépendance : Request est fourni par Symfony et contient 
        // toutes les informations concernant la requête HTTP entrante.
        Request $request
    ): Response {
        // Récupère la valeur du paramètre 'latestAlert' depuis la chaîne de requête (query string) 
        // de l'URL. Si 'latestAlert' n'est pas présent, la valeur sera null.
        $latestAlert = $request->query->get('latestAlert');

        // Renvoie une réponse HTTP contenant le rendu de la vue 'alerte/confirmation.html.twig'. 
        // Cette vue reçoit 'latestAlert' en tant que variable qu'elle pourra utiliser pour afficher 
        // des informations pertinentes à l'utilisateur.
        return $this->render('alerte/confirmation.html.twig', [
            'latestAlert' => $latestAlert
        ]);
    }


    #[Route('/get-latest-alert', name: 'get_latest_alert', methods: ['GET'])]
    public function latestAlert(
        // Injection de dépendance: L'objet AlerteRepository est automatiquement instancié et injecté 
        // par Symfony. Il est utilisé pour effectuer des requêtes liées à l'entité Alerte.
        AlerteRepository $alerteRepository
    ): Response {
        // Utilise la méthode `findLatestAlert()` de l'objet `$alerteRepository` pour 
        // récupérer la dernière alerte enregistrée dans la base de données.
        $latestAlert = $alerteRepository->findLatestAlert();
        
        // Prépare un tableau associatif pour représenter les données de l'alerte.
        // Les données sont structurées pour une réponse JSON.
        $data = [
            'id' => $latestAlert->getId(),
            'ligne' => $latestAlert->getLigne(),
            // Formate la date de l'alerte en chaîne de caractères pour une meilleure 
            // lisibilité et compatibilité avec les standards JSON.
            'alerteDate' => $latestAlert->getAlerteDate()->format('Y-m-d H:i:s'),
            'sens' => $latestAlert->getSens(),
            'user' => [
                // Récupère et structure les informations de l'utilisateur associé à l'alerte.
                'id' => $latestAlert->getUser()->getId(),
                'username' => $latestAlert->getUser()->getPseudo()
            ]
        ];

        // Renvoie une réponse JSON contenant les données de la dernière alerte.
        // Utilise le code de statut HTTP 200 (OK) pour indiquer que la requête a réussi.
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }


    /**
     * La route '/getAlertDetails/{alertId}' attend un identifiant d'alerte en tant que paramètre.
     * Cette fonction renvoie un objet JSON contenant les détails de l'alerte.
     */
    #[Route('/getAlertDetails/{alertId}', name: 'get_alert_details')]
    public function getAlertDetails(
        // Paramètre typé pour garantir que l'ID de l'alerte est bien un entier.
        int $alertId,
        // Injection de dépendance: L'objet AlerteRepository est automatiquement instancié et injecté 
        // par Symfony pour effectuer des requêtes sur l'entité Alerte.
        AlerteRepository $alerteRepository
    ): JsonResponse {
        // Récupère l'entité Alerte correspondant à l'ID donné à l'aide de la méthode `find()`.
        $alerte = $alerteRepository->find($alertId);
        
        // Vérifie si l'alerte a été trouvée dans la base de données.
        if (!$alerte) {
            // Si l'alerte n'est pas trouvée, renvoie une réponse JSON avec un message d'erreur
            // et un code de statut HTTP 404 (Not Found).
            return new JsonResponse(['error' => 'Alerte non trouvée'], 404);
        }
        
        // Si l'alerte est trouvée, prépare un tableau associatif contenant les détails 
        // pertinents de l'alerte pour une réponse JSON.
        $alerteData = [
            'alertId' => $alerte->getId(),
            'ligne' => $alerte->getLigne(),
            'sens' => $alerte->getSens(),
            // Récupère le pseudo de l'utilisateur associé à l'alerte.
            'user' => $alerte->getUser()->getPseudo(),
            // Formate la date de l'alerte en chaîne de caractères pour garantir la lisibilité 
            // et la compatibilité avec le format JSON.
            'alerteDate' => $alerte->getAlerteDate()->format('Y-m-d H:i:s'),
        ];
        
        // Renvoie une réponse JSON contenant les détails de l'alerte. Utilise le code de statut 
        // HTTP 200 (OK) par défaut pour indiquer que la requête a été traitée avec succès.
        return new JsonResponse($alerteData);
    }


    /**
     * Contrôleur responsable de la récupération de l'identifiant de la dernière alerte créée.
     * 
     * La route 'getLatestAlertId' est mappée à cette fonction pour répondre aux requêtes HTTP.
     * Elle renvoie un objet JSON contenant l'identifiant de la dernière alerte ajoutée.
     * Si aucune alerte n'est trouvée, elle renvoie `null` pour l'ID.
     */
    #[Route('getLatestAlertId', name: 'get_latest_alert_id')]
    public function getLatestAlertId(
        // Injection de dépendance: L'objet AlerteRepository est automatiquement instancié et injecté 
        // par Symfony pour effectuer des requêtes sur l'entité Alerte.
        AlerteRepository $repository
    ): JsonResponse {
        // Récupère l'ID de la dernière alerte créée à l'aide de la méthode `findLatestAlertId()`.
        // On suppose que cette méthode renvoie un tableau associatif contenant l'ID de l'alerte
        // sous la clé 'id', ou `null` si aucune alerte n'est trouvée.
        $result = $repository->findLatestAlertId();
        
        // Extraie l'identifiant de l'alerte du résultat ou assigne `null` si aucun résultat n'est trouvé.
        $alertId = $result ? $result['id'] : null;

        // Renvoie une réponse JSON contenant soit l'identifiant de la dernière alerte soit `null`.
        // Utilise le code de statut HTTP 200 (OK) par défaut pour indiquer que la requête a été traitée avec succès.
        return new JsonResponse(['id' => $alertId]);
    }

    /**
     * Contrôleur API responsable de la récupération des informations concernant la dernière alerte créée.
     *
     * Cette fonction est mappée à la route '/latest-alert' et répond aux requêtes HTTP GET.
     * Si une alerte est trouvée, elle renvoie les informations pertinentes de cette alerte au format JSON.
     * Si aucune alerte n'est trouvée, elle renvoie un message d'erreur avec le code de statut HTTP 404.
     */
    #[Route('/latest-alert', name: 'latest_alert')]
    public function getLatestAlertInfo(
        // Injection de dépendance: L'objet AlerteRepository est automatiquement instancié et injecté 
        // par Symfony pour permettre des requêtes spécifiques sur l'entité Alerte.
        AlerteRepository $alerteRepository
    ): JsonResponse {
        // Récupère la dernière alerte créée à l'aide de la méthode `findLatestAlert()`.
        // On suppose que cette méthode renvoie un objet représentant la dernière alerte
        // ou `null` si aucune alerte n'est trouvée.
        $latestAlert = $alerteRepository->findLatestAlert();

        // Vérifie si aucune alerte n'est trouvée.
        if (!$latestAlert) {
            // Dans le cas où aucune alerte n'est trouvée, renvoie un objet JSON
            // avec un message d'erreur et le code de statut HTTP 404.
            return new JsonResponse(['error' => 'Aucune alerte trouvée'], 404);
        }

        // Si une alerte est trouvée, construit un tableau associatif avec les informations pertinentes 
        // de l'alerte pour préparer la réponse JSON.
        $data = [
            'id' => $latestAlert->getId(),
            'ligne' => $latestAlert->getLigne(),
            'alerteDate' => $latestAlert->getAlerteDate()->format('Y-m-d H:i:s'),
            'sens' => $latestAlert->getSens(),
            'user' => [
                'id' => $latestAlert->getUser()->getId(),
                // Ici, on utilise getPseudo() pour obtenir le pseudonyme de l'utilisateur. 
                'username' => $latestAlert->getUser()->getPseudo()
            ]
        ];

        // Renvoie une réponse JSON contenant les informations de la dernière alerte.
        // Utilise le code de statut HTTP 200 (OK) par défaut pour indiquer que la requête a été traitée avec succès.
        return new JsonResponse($data);
    }

    #[Route('/alertes', name: 'list_alertes', methods: ['GET'])]
    public function list(AlerteRepository $alerteRepository): Response
    {
        $alertes = $alerteRepository->findBy([], ['alerteDate' => 'DESC']);
        
        return $this->render('alerte/alertes.html.twig', [
            'alertes' => $alertes,
        ]);
        
    }

    #[Route('/statistiques', name: 'statistiques_alertes', methods: ['GET'])]
    public function statistiques(AlerteRepository $alerteRepository): Response
    {
        $statistiques = $alerteRepository->getAlerteStatistics();
        
        $lignes = $alerteRepository->getAllDistinctLignes();
        
        return $this->render('alerte/statistiques.html.twig', [
            'statistiques' => $statistiques,
            'lignes' => $lignes, // Passe la variable lignes à la vue
        ]);
    }
    
    

}