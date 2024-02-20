<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuTarifController extends AbstractController
{
    /**
     * @Route("/menu-tarif", name="app_menu_tarif")
     */
    public function menuTarif(): Response
    {
        return $this->render('Tarifs/menuTarif.html.twig');
    }
}
