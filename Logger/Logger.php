<?php

namespace Oh\FormErrorLogBundle\Logger;

use Oh\FormErrorLogBundle\Logger\ErrorLogInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class Logger implements ErrorLogInterface
{
    /**
     * The monolog logger
     * @var Monolog\Logger
     */
    private $logger;
    
    public function __construct($logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * 
     * @param string $formName
     * @param string $key
     * @param string $error
     * @param string $value
     */
    public function log($formName, $key, $error, $value = '')
    {
        
        $this->logger->notice(strtr('Error in form "%1" in position "%2": "%3" with value "%4"', array(
            '%1'=>$formName,
            '%2'=>$key,
            '%3'=>$error,
            '%4'=>$value
        )));
        
    }
    
}