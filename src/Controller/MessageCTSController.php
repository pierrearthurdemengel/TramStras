<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MessageCTSController extends AbstractController
{
    private $client;
    private $logger;
    private $usernameCTS;
    private $passwordCTS;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger, string $usernameCTS, string $passwordCTS)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->usernameCTS = $usernameCTS;
        $this->passwordCTS = $passwordCTS;
    }

    #[Route('/messageCTS', name: 'messageCTS')]
    public function fetchMessages(Request $request): Response
    {
        $requestorRef = $this->usernameCTS;

        $response = $this->client->request('GET', 'https://api.cts-strasbourg.eu/v1/siri/2.0/general-message', [
            'query' => [
                'RequestorRef' => $requestorRef,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth_basic' => [$this->usernameCTS, $this->passwordCTS],
        ]);

        $data = $response->toArray();

        $messages = $data['ServiceDelivery']['GeneralMessageDelivery'][0]['InfoMessage'] ?? [];

        return $this->render('messageCTS/index.html.twig', [
            'messages' => $messages,
        ]);
    }
}
