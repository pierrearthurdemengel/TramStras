<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Repository\AlerteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', priority: 10)]

    public function index(AlerteRepository $alerteRepo): Response
    {
        $latestAlert = $alerteRepo->findOneBy([], ['id' => 'DESC']);
        return $this->render('home/index.html.twig', [
            'latestAlert' => $latestAlert
        ]);
    }
}