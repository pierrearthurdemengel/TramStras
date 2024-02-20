<?php

namespace App\Controller;

use Exception;
use App\Entity\Marker;
use App\Entity\Alerte;
use Psr\Log\LoggerInterface;
use App\Repository\AlerteRepository;
use App\Repository\MarkerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;


class MapController extends AbstractController
{
    private $logger;           // Logger pour tracer les messages d'application.
    private $usernameCTS;      // Nom d'utilisateur pour l'API CTS.
    private $passwordCTS;      // Mot de passe pour l'API CTS.

    public function __construct(LoggerInterface $logger, string $usernameCTS, string $passwordCTS)
    {
        // Initialisation des propriétés du contrôleur.
        $this->logger = $logger;
        $this->usernameCTS = $usernameCTS;
        $this->passwordCTS = $passwordCTS;   
    }

    #[Route('/map', name: 'points_map')]
    public function index(MarkerRepository $markerRepository, AlerteRepository $alerteRepo): Response
    {
        // Récupération de la dernière alerte depuis la base de données
        $latestAlert = $alerteRepo->findOneBy([], ['id' => 'DESC']);

        // Initialisation des variables avec les informations d'authentification pour l'API CTS
        $url = 'https://api.cts-strasbourg.eu/v1/siri/2.0/stoppoints-discovery';
        $usernameCTS = $this->usernameCTS;
        $passwordCTS = $this->passwordCTS;

         // Configuration des options de la requête HTTP pour l'API
        $options = [
            'http' => [
                'header' => "Authorization: Basic " . base64_encode($usernameCTS . ':' .$passwordCTS) . "\r\n",
                'ignore_errors' => true
            ]
        ];

        // Envoi de la requête HTTP à l'API
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        // Si la réponse n'est pas fausse, on traite les données
        if ($response !== false) {
            $data = json_decode($response, true);

            // Vérification de l'existence des points d'arrêt dans la réponse
            if (isset($data['StopPointsDelivery']['AnnotatedStopPointRef'])) {
                $apiStopPoints = $data['StopPointsDelivery']['AnnotatedStopPointRef'];

                $markers = [];
                $lines = [];
                $polylines = [];

                // Parcourir les points d'arrêt de l'API
                foreach ($apiStopPoints as $apiStopPoint) {
                    // Extraction des données pour chaque arrêt
                    $latitude = $apiStopPoint['Location']['Latitude'];
                    $longitude = $apiStopPoint['Location']['Longitude'];
                    $coordinates[] = [$latitude, $longitude];
                    $stopName = $apiStopPoint['StopName'];
                    $linesDestinations = $apiStopPoint['LinesDestinations'] ?? [];

                    // Récupérer les destinations des lignes
                    $destinations = [];
                    foreach ($linesDestinations as $lineDestination) {
                        $destination = $lineDestination['DestinationName'];
                        $destinations[] = $destination;
                    }

                    // Créer un marqueur pour chaque point d'arrêt de l'API
                    $markers[] = [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'stopName' => $stopName,
                        'stopCode' => $apiStopPoint['Extension']['StopCode'],
                        'linesDestinations' => $destinations,
                        'isCustom' => false, // Marqueur de l'API, icône par défaut utilisée
                    ];

                    // Traitement des noms de lignes, suppression du préfixe "Ligne "
                    $lineName = $apiStopPoint['Lines'] ?? '';
                    $lineName = str_replace('Ligne ', '', $lineName); // Supprime le préfixe "Ligne "

                    // Groupement des points par nom de ligne
                    if (!isset($lines[$lineName])) {
                        $lines[$lineName] = [];
                    }

                    $lines[$lineName][] = [$latitude, $longitude];
                }

                // Récupérer les points ajoutés par les utilisateurs depuis la base de données
                $userMarkers = $markerRepository->findRecentMarkers();

                // Convertir les marqueurs personnalisés en format compatible
                foreach ($userMarkers as $userMarker) {
                    $markers[] = [
                        'latitude' => $userMarker->getLatitude(),
                        'longitude' => $userMarker->getLongitude(),
                        'stopName' => '', // le nom personnalisé si nécessaire
                        'stopCode' => '', // le code d'arrêt personnalisé si nécessaire
                        'linesDestinations' => [], // les destinations de lignes personnalisées si nécessaire
                        'isCustom' => true, // icône spécifique
                        'text' => $userMarker->getText(),  // le texte associé au marqueur personnalisé
                    ];
                }

                // Créer les objets de polylinéaire pour chaque ligne
                foreach ($lines as $lineName => $lineCoordinates) {
                    $polyline = [
                        'lineName' => $lineName,
                        'coordinates' => $lineCoordinates,
                    ];

                    $polylines[] = $polyline;
                }

                // Rendu de la vue avec les données préparées
                return $this->render('map/index.html.twig', [
                    'markers' => $markers,
                    'lines' => $lines,
                    'polylines' => $polylines,
                    'latestAlert' => $latestAlert
                ]);
            } else {
                var_dump($response);

                return new Response('Échec de récupération des données depuis l\'API', 500);
            }
        } else {
            // Si la requête à l'API échoue, retourner une erreur
            return new Response('Échec de récupération des données depuis l\'API', 500);
        }
    }


    
    #[Route('/horaires/{stopCode}', name: 'horaires_map')]
    public function horaires_map(string $stopCode): Response
    {
        try {
            // Construction de l'URL pour interroger l'API CTS sur les horaires d'un point d'arrêt spécifique
            $url = 'https://api.cts-strasbourg.eu/v1/siri/2.0/estimated-timetable?StopPointRef=' . $stopCode;
            
            // Paramètres d'authentification pour l'API
            $usernameCTS = $this->usernameCTS;
            $passwordCTS = $this->passwordCTS;
            $requestorRef =  $usernameCTS;
            $previewInterval = 'PT2H';
            $includeGeneralMessage = 'true';
            $includeFLUO67 = 'false';
            $removeCheckOut = 'false';
            $getStopIdInsteadOfStopCode = 'false';
    
            // Paramètres additionnels pour la requête à l'API
            $queryParameters = [
                'RequestorRef' => $requestorRef,
                'PreviewInterval' => $previewInterval,
                'IncludeGeneralMessage' => $includeGeneralMessage,
                'IncludeFLUO67' => $includeFLUO67,
                'RemoveCheckOut' => $removeCheckOut,
                'GetStopIdInstedOfStopCode' => $getStopIdInsteadOfStopCode,
            ];
    
            // Ajout des paramètres à l'URL
            $url .= '&' . http_build_query($queryParameters);
    
            // Création du client HTTP avec authentification
            $client = HttpClient::create([
                'auth_basic' => [$usernameCTS, $passwordCTS],
            ]);
    
            // Exécution de la requête GET
            $response = $client->request('GET', $url);
            $data = $response->toArray();
    
            // Initialisation du tableau qui contiendra les horaires
            $stopTimes = [];

            // Vérification de la présence de données d'horaire dans la réponse
            if (isset($data['ServiceDelivery']['EstimatedTimetableDelivery'])) {
                $timetableDelivery = $data['ServiceDelivery']['EstimatedTimetableDelivery'];
    
                // Parcours des trames de version pour extraire les données d'horaire
                foreach ($timetableDelivery as $versionFrame) {
                    if (isset($versionFrame['EstimatedJourneyVersionFrame'])) {
                        $journeyFrames = $versionFrame['EstimatedJourneyVersionFrame'];
    
                        $estimatedJourneys = [];
                        foreach ($journeyFrames as $journeyFrame) {
                            if (isset($journeyFrame['EstimatedVehicleJourney'])) {
                                $estimatedJourneys = array_merge($estimatedJourneys, $journeyFrame['EstimatedVehicleJourney']);
                            }
                        }
    
                        // Parcours des voyages estimés pour obtenir les heures d'appel
                        foreach ($estimatedJourneys as $estimatedJourney) {
                            if (isset($estimatedJourney['EstimatedCalls'])) {
                                $estimatedCalls = $estimatedJourney['EstimatedCalls'];
    
                                foreach ($estimatedCalls as $estimatedCall) {
                                    if ($estimatedCall['StopPointRef'] == $stopCode) {
                                        $stopPointName = $estimatedCall['StopPointName'];
                                        $expectedDepartureTime = new \DateTime($estimatedCall['ExpectedDepartureTime']);
                                        $destinationName = $estimatedCall['DestinationName'];
    
                                        // Ajoute l'heure d'arrêt au tableau uniquement si elle se situe dans le futur
                                        if ($expectedDepartureTime > new \DateTime()) {
                                            $stopTimes[] = [
                                                'stopPointName' => $stopPointName,
                                                'expectedDepartureTime' => $expectedDepartureTime->format('H:i'),
                                                'destinationName' => $destinationName,
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
    
            // Retourne les horaires sous forme de JSON
            return $this->json([
                'stopTimes' => $stopTimes,
                'stopCode' => $stopCode,
            ]);
    
        } catch (\Exception $e) {
            // En cas d'exception, retourne un message d'erreur sous forme de JSON
            return $this->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    #[Route("/post/create", name: "post_create", methods: ["POST"])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération des données de la requête
        $latitude = floatval($request->request->get('lat'));      // Conversion en flottant de la latitude
        $longitude = floatval($request->request->get('lng'));     // Conversion en flottant de la longitude
        $text = $request->request->get('text');                   // Récupération du texte associé au marqueur
    
        // Instanciation et initialisation d'un nouvel objet Marqueur
        $marker = new Marker();
        $marker->setLatitude($latitude);
        $marker->setLongitude($longitude);
        $marker->setText($text);
        $marker->setUser($this->getUser());  // Récupère et assigne l'utilisateur actuellement authentifié
        $marker->setCreationDate(new \DateTime());  // Assignation de la date et heure actuelle comme date de création
    
        // Persistance et enregistrement du nouvel objet Marqueur dans la base de données
        $entityManager->persist($marker);
        $entityManager->flush();
    
        // Construction du tableau contenant les données du marqueur pour la réponse JSON
        $data = [
            'id' => $marker->getId(),  // Récupération de l'ID généré par la BDD pour le nouveau marqueur
            'user_id' => $marker->getUser()->getId(),  // Récupération de l'ID de l'utilisateur associé
            'latitude' => $marker->getLatitude(),
            'longitude' => $marker->getLongitude(),
            'text' => $marker->getText(),
            'creation_date' => $marker->getCreationDate(),
        ];
    
        // Retour de la réponse contenant les données du marqueur au format JSON
        return new Response(json_encode($data));
    }

}