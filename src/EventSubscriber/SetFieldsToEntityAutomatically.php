<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SetFieldsToEntityAutomatically implements EventSubscriberInterface
{

  public function __construct(private readonly TokenStorageInterface $tokenStorate)
  {
  }

  public static function getSubscribedEvents(): array
  {
    return [
      KernelEvents::VIEW => ['setFieldToEntity', EventPriorities::PRE_WRITE]
    ];
  }

  public function setFieldToEntity(ViewEvent $event)
  {
    $entity = $event->getControllerResult();
    $method = $event->getRequest()->getMethod();
    $token = $this->tokenStorate->getToken();

    if ($token !== null) {
      $user = $token->getUser();
      if (
        $user !== null &&
        method_exists($entity, 'setOwner') &&
        in_array($method, [Request::METHOD_POST, Request::METHOD_PATCH])
      ) {
        $entity->setOwner($user);
      }

      if (
        $user !== null &&
        method_exists($entity, 'setRequestingUser') &&
        in_array($method, [Request::METHOD_POST, Request::METHOD_PATCH])
      ) {
        $entity->setRequestingUser($user);
      }
    }

    if (
      method_exists($entity, 'setSlug') &&
      in_array($method, [Request::METHOD_POST, Request::METHOD_PATCH])
    ) {
      $entity->setSlug($entity->getTitle());
    }

    if (
      method_exists($entity, 'setUpdatedAt') &&
      in_array($method, [Request::METHOD_PATCH])
    ) {
      $entity->setUpdatedAt(new \DateTime());
    }
  }
}
