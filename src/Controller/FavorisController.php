<?php

namespace App\Controller;

use App\Entity\Favoris;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FavorisController extends AbstractController
{
    #[Route('/favoris', name: 'app_favoris')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $favoris = $doctrine->getRepository(Favoris::class)->findAll();

        return $this->render('favoris/index.html.twig', [
            'favoris' => $favoris,
        ]);
    }
}
