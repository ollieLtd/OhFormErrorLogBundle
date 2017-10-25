<?php

namespace Oh\FormErrorLogBundle\EventListener;

use Oh\FormErrorLogBundle\Logger\ErrorLogInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;

class ErrorLogSubscriber implements EventSubscriberInterface
{
    /**
     * Whatever you want to use as the logger
     * @var Oh\FormErrorLogBundle\Logger\ErrorLogInterface 
     */
    private $logger;
    
    /**
     * This is to log the request variables if the form data can't be logged
     * @var Symfony\Component\HttpFoundation\Request 
     */
    private $request;

    public function __construct(ErrorLogInterface $logger, RequestStack $request)
    {
        $this->logger = $logger;
        $this->request = $request->getMasterRequest();
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::POST_SUBMIT => 'postSubmit');
    }

    /**
     * 
     * @param \Symfony\Component\Form\FormEvent $event
     * @return null
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        
        $errors = $this->getErrorMessages($form);
        
        if(empty($errors)) {
            return null;
        }
        
        $formName = $form->getName();

        foreach($errors as $key => $error) {
            $uri = $this->request->getUri();
            $this->logger->log($formName, $key, $error['messages'], $error['value'], $uri);
        }
        
        return null;
    }
    
    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
        
        $errors = array();
        
        /* Get the errors from this FormType */
        foreach ($form->getErrors() as $key => $error) {
            $data = $form->getData();
            
            /* If it's a bound object then we need to log it somehow */
            if(is_object($data))
            {
                // JsonSerializable is for php 5.4
                if(class_exists('\JsonSerializable', false) && $data instanceof \JsonSerializable) {
                    $data = json_encode($data);
                }
                // otherwise we could just see if that method exists
                elseif(method_exists($data, 'jsonSerialize'))
                {
                    $data = json_encode($data->jsonSerialize());
                }
                // some people create a toArray() method
                elseif(method_exists($data, 'toArray') && is_array($array = $data->toArray()))
                {
                    // JSON_PRETTY_PRINT is > PHP 5.4
                    if(defined('JSON_PRETTY_PRINT')) {
                        $data = json_encode($array, JSON_PRETTY_PRINT);
                    }else {
                        $data = json_encode($array);
                    }
                    
                }
                // lets try to serialize
                // this could be risky if the object is too large or not implemented correctly
                elseif(method_exists($data, '__sleep') || $data instanceof Serializable) {
                    $data = @serialize($data);
                }
                // lets see if we can get the form data from the request
                elseif($this->request->request->has($form->getName())) {
                    // lets log it
                    $data = 'POST DATA: '.json_encode($this->request->request->get($form->getName()));
                }
                // it looks like the object isnt loggable
                else {
                    $data = '';
                }
            }
            $errors[$key] = array('messages'=>$error->getMessage(), 'value'=>$data);
        }
        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $childErrors = $this->getErrorMessages($child);
                    $messages = $values = array();
                    foreach($childErrors as $childError) {
                        $messages[] = $childError['messages'];
                        $values[] = $childError['value'];
                    }

                    // if there's more than 1 error or value on a field then we can log them all
                    $messages = implode(' | ', $messages);

                    $recursiveImplode = function($glue, array $value) use (&$recursiveImplode) {
                        $ret = '';
                        foreach ($value as $val) {
                            $ret .= $glue . (is_array($val) ? $recursiveImplode($glue, $val) : $val);
                        }

                        return strpos($ret, $glue) === 0 ? substr($ret, strlen($glue)) : $ret;
                    };

                    $values = $recursiveImplode(' | ', $values);

                    $errors[$child->getName()] = array('messages'=>$messages, 'value'=>$values);
                }
            }
        }
        
        return $errors;
    }
}
