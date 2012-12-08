<?php

namespace Oh\FormErrorLogBundle\EventListener;

use Oh\FormErrorLogBundle\Logger\ErrorLogInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

//use Symfony\Component\Serializer\Serializer;
//use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
//use Symfony\Component\Serializer\Encoder\JsonEncoder;


class ErrorLogSubscriber implements EventSubscriberInterface
{
    private $logger;
    private $request;

    public function __construct(ErrorLogInterface $logger, $request)
    {
        $this->logger = $logger;
        $this->request = $request;
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::POST_BIND => 'postBind');
    }

    public function postBind(FormEvent $event)
    {
        $form = $event->getForm();
        
        $errors = $this->getErrorMessages($form);
        
        if(empty($errors)) {
            return null;
        }
        
        $formName = $form->getName();

        foreach($errors as $key => $error) {
            $this->logger->log($formName, $key, $error['messages'], $error['value']);
        }
        
        return null;
    }
    
    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
        
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $data = $form->getData();
            if(is_object($data))
            {
                
                if(class_exists('\JsonSerializable', false) && $data instanceof \JsonSerializable) {
                    $data = json_encode($data);
                }
                elseif(method_exists($data, 'jsonSerialize'))
                {
                    $data = $data->jsonSerialize();
                }
                elseif(method_exists($data, '__sleep') || $data instanceof Serializable) {
                    $data = @serialize($data);
                }
                elseif($this->request->request->has($form->getName())) {
                    //lets just get the form data
                    $data = json_encode($this->request->request->get($form->getName()));
                }else {
                    $data = '';
                }
            }
            $errors[$key] = array('messages'=>$error->getMessage(), 'value'=>$data);
        }
        if ($form->hasChildren()) {
            foreach ($form->getChildren() as $child) {
                if (!$child->isValid()) {
                    $childErrors = $this->getErrorMessages($child);
                    $messages = $values = array();
                    foreach($childErrors as $childError) {
                        $messages[] = $childError['messages'];
                        $values[] = $childError['value'];
                    }

                    $messages = implode(' | ', $messages);
                    $values = implode(' | ', $values);

                    $errors[$child->getName()] = array('messages'=>$messages, 'value'=>$values);
                }
            }
        }
        
        return $errors;
    }
}