<?php

namespace App\EventSubscriber;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HttpResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @return \string[][][]
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [['onKernelController']],
        ];
    }

    /**
     * @param ControllerEvent $event
     * @return void
     */
    public function onKernelController(ControllerEvent $event)
    {
        $controllers = $event->getController();

        foreach ($controllers as $controller) {
            if (!$controller instanceof UserTasksAwareController) {
                return;
            }

            $log = new Log();

            $request = $event->getRequest();

            $log
                ->setRoute($request->getPathInfo())
                ->setIp($request->getClientIp())
                ->setDate(new \DateTime());

            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }

    }
}
