<?php

namespace Oh\FormErrorLogBundle\EventListener;

use Oh\FormErrorLogBundle\Logger\ErrorLogInterface;
use Oh\FormErrorLogBundle\Logger\SerializeData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;

class ErrorLogSubscriber implements EventSubscriberInterface
{
    use SerializeData;

    /**
     * Whatever you want to use as the logger
     * @var ErrorLogInterface
     */
    private $logger;

    /**
     * This is to log the request variables if the form data can't be logged
     * @var Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @param ErrorLogInterface $logger
     * @param RequestStack $request
     */
    public function __construct(ErrorLogInterface $logger, RequestStack $request)
    {
        $this->logger = $logger;
        $this->request = $request->getMasterRequest();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'postSubmit',
        ];
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        $errors = $this->getErrorMessages($form);

        if (empty($errors)) {
            return;
        }

        $formName = $form->getName();

        foreach ($errors as $key => $error) {
            $uri = $this->request->getUri();
            $this->logger->log($formName, $key, $error['messages'], $error['value'], $uri);
        }
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     * @return array
     */
    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = [];

        /* Get the errors from this FormType */
        foreach ($form->getErrors() as $key => $error) {
            $data = $form->getData();

            $serializedData = $this->serializeData($data);
            if (empty($serializedData)) {
                $formData = $this->request->request->has($form->getName())
                    ? $this->request->request->get($form->getName())
                    : null;
                $serializedData = 'POST DATA: '.json_encode($formData);
            }

            $errors[$key] = [
                'messages' => $error->getMessage(),
                'value' => $serializedData,
            ];
        }

        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $childErrors = $this->getErrorMessages($child);
                    $messages = $values = [];
                    foreach($childErrors as $childError) {
                        $messages[] = $childError['messages'];
                        $values[] = $childError['value'];
                    }

                    // if there's more than 1 error or value on a field then we can log them all
                    $messages = implode(' | ', $messages);
                    $values = implode(' | ', $values);

                    $errors[$child->getName()] = [
                        'messages' => $messages,
                        'value' => $values,
                    ];
                }
            }
        }

        return $errors;
    }
}
