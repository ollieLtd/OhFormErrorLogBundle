<?php

namespace Oh\FormErrorLogBundle\Logger;

interface ErrorLogInterface
{
    /**
     * Lets log the error here
     * @param string $formName The name of the form
     * @param string $key The name of the form field
     * @param string $error The error
     * @param string $value The value of the form field or a string representation of an object
     */
    public function log($formName, $key, $error, $value = '', $uri = '');
}