<?php

namespace App\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
final class DynamicRelationListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        // https://blog.theodo.com/2013/11/dynamic-mapping-in-doctrine-and-symfony-how-to-extend-entities/
        // ...
    }
}
