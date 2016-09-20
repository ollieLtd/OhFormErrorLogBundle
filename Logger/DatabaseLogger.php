<?php

namespace Oh\FormErrorLogBundle\Logger;

use Doctrine\ORM\EntityManagerInterface;
use Oh\FormErrorLogBundle\Entity\FormErrorLogEntityInterface;
use Oh\FormErrorLogBundle\Event\Events;
use Oh\FormErrorLogBundle\Event\PrePersistEntityEvent;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DatabaseLogger implements ErrorLogInterface
{
    private $em;

    private $entityClass;

    private $eventDispatcher;

    /**
     * @param EntityManagerInterface $em
     * @param $entityClass
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManagerInterface $em, $entityClass, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function log($formName, $key, $error, $value = '', $uri = '')
    {
        if ($this->entityClass == 'Oh\FormErrorLogBundle\Entity\FormErrorLogEntityInterface') {
            throw new InvalidArgumentException('You need to update your %oh_form_error_log.db.entity.class% parameter to your own class. See the README for help.');
        }

        /** @var FormErrorLogEntityInterface $entity */
        $entity = new $this->entityClass;

        $entity->setFormName($formName);
        $entity->setField($key);
        $entity->setError($error);
        $entity->setValue($value);
        // for BC
        if (method_exists($entity, 'setUri')) {
            $entity->setUri($uri);
        }

        $this->eventDispatcher->dispatch(Events::PRE_PERSIST, new PrePersistEntityEvent($entity));

        $this->em->persist($entity);

        $this->em->flush($entity);
    }
}