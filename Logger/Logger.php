<?php

namespace Oh\FormErrorLogBundle\Logger;

use Monolog\Logger;
use Oh\FormErrorLogBundle\Logger\ErrorLogInterface;

class Logger implements ErrorLogInterface
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $formName
     * @param string $key
     * @param string $error
     * @param string $value
     * @return void
     */
    public function log($formName, $key, $error, $value = '', $uri = '')
    {
        $this->logger->notice(strtr('%0 - Error in form "%1" in position "%2": "%3" with serialized value "%4"', array(
            '%0' => $uri,
            '%1' => $formName,
            '%2' => $key,
            '%3' => $error,
            '%4' => serialize($value),
        )));
    }
}
