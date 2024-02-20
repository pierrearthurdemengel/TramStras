<?php

namespace App\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GlobalVariableExtension extends AbstractExtension
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('latestAlert', [$this, 'getLatestAlert']),
        ];
    }

    public function getLatestAlert()
    {
        $latestAlert = $this->entityManager->getRepository(Alerte::class)->findOneBy([], ['id' => 'DESC']);
        return $latestAlert;
    }
}
