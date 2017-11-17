<?php

namespace Oh\FormErrorLogBundle\Event;

use Oh\FormErrorLogBundle\Entity\FormErrorLogEntityInterface;
use Symfony\Component\EventDispatcher\Event;

class PrePersistEntityEvent extends Event
{
    /**
     * @var FormErrorLogEntityInterface
     */
    private $entity;

    /**
     * @param FormErrorLogEntityInterface $entity
     */
    public function __construct(FormErrorLogEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return FormErrorLogEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }
}