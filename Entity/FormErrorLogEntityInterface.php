<?php

namespace Oh\FormErrorLogBundle\Entity;

interface FormErrorLogEntityInterface
{
    public function setFormName($formName);

    public function setField($field);

    public function setError($error);
    
}