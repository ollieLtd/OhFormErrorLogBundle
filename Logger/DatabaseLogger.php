<?php

namespace Oh\FormErrorLogBundle\Logger;

use Oh\FormErrorLogBundle\Logger\ErrorLogInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class DatabaseLogger implements ErrorLogInterface
{
    private $em;
    private $entityClass;
    
    public function __construct($em, $entityClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
    }
    
    public function log($formName, $key, $error, $value = '', $uri = '')
    {
        if($this->entityClass == 'Oh\FormErrorLogBundle\Entity\FormErrorLogEntityInterface') {
            throw new InvalidArgumentException('You need to update your %oh_form_error_log.db.entity.class% parameter to your own class. See the README for help.');
        }
        $entity = new $this->entityClass;
        
        $entity->setFormName($formName);
        $entity->setField($key);
        $entity->setError($error);
        $entity->setValue($value);
        // for BC
        if(method_exists($entity, 'setUri')) {
            $entity->setUri($uri);
        }
        
        $this->em->persist($entity);
        
        $this->em->flush($entity);
        
    }
    
}