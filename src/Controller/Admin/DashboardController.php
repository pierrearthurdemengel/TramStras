<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Alerte;
use App\Entity\Marker;
use App\Entity\Categorie;
use App\Entity\ImagesUsers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('SymfonyTramStras - Administration')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Alertes', 'fa-solid fa-circle-exclamation', Alerte::class);
        yield MenuItem::linkToCrud('Marqueurs', 'fa-solid fa-location-dot', Marker::class);
        yield MenuItem::linkToCrud('Catégories', 'fa-solid fa-bars', Categorie::class);
        yield MenuItem::linkToCrud('Topics', 'fa-solid fa-list', Topic::class);
        yield MenuItem::linkToCrud('Posts', 'fa-solid fa-paragraph', Post::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Images Users', 'fa fa-images', ImagesUsers::class);
    }
}
