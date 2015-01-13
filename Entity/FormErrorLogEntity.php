<?php

namespace Oh\FormErrorLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oh\FormErrorLogBundle\Entity\FormErrorLogEntityInterface;

/**
 * @ORM\Table(name="form_error_log")
 * @ORM\Entity
 */
class FormErrorLog implements FormErrorLogEntityInterface
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="form_name", type="string", length=255)
     * @var type 
     */
    private $form_name;

    /**
     * @var string $field
     * 
     * @ORM\Column(name="field", type="string", length=255)
     */
    private $field;

    /**
     * @var string $error
     * 
     * @ORM\Column(name="error", type="string", length=2000)
     */
    private $error;

    /**
     * @var string $error
     * 
     * @ORM\Column(name="value", type="string", length=2000)
     */
    private $value;

    /**
     * @var string $uri
     *
     * @ORM\Column(type="string", length=512)
     */
    private $uri;

    public function getFormName()
    {
        return $this->form_name;
    }

    public function setFormName($formName)
    {
        $this->form_name = $formName;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    public function getUri()
    {
        return $this->uri;
    }

}
