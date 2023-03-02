<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SetFieldsToEntityAutomatically implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::VIEW => ['setFieldToEntity', EventPriorities::PRE_WRITE]
    ];
  }

  public function setFieldToEntity()
  {
    die('here');
  }
}
