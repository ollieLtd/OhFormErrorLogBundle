<?php

namespace Oh\FormErrorLogBundle\Logger;

interface ErrorLogInterface
{
    /**
     * Lets log the error here
     */
    public function log($formName, $key, $error, $value = '');
}