<?php 

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class AlertSubscriber implements EventSubscriberInterface
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'onKernelView',
        ];
    }

    public function onKernelView(KernelEvent $event)
    {
        $latestAlert = $this->getLatestAlert();
        $event->getRequest()->attributes->set('latestAlert', $latestAlert);
    }

    private function getLatestAlert()
    {
        $alertRepository = $this->entityManager->getRepository(Alert::class); // Replace "Alert" with your actual entity class
        $latestAlert = $alertRepository->findOneBy([], ['id' => 'DESC']);

        return $latestAlert ?? null;
    }
}
