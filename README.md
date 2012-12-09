OhFormErrorLogBundle
=================

Log form errors.

Functional testing your forms is all well and good, but how do you know your 
users are using it correctly? This plugin can log the errors your users are
making so that you can spot any usability problems.

WARNING: If the error is on the whole form this bundle tries to json_encode your 
bound entity. If it can't be json_encoded it will try to serialize before
finally logging the whole _POST request for the form. THIS IS A SECURITY RISK IF 
USED ON FORMS CONTAINING SENSITIVE DATA, LIKE PASSWORDS OR CREDIT CARD 
INFORMATION. If this is the case, you should implement the Serializeable or 
JsonSerializable (PHP 5.4) interfaces on your bound objects to block out the 
sensitive data.

Installation
------------

This bundle is alpha stability due to the lack of testing on different form 
types. Your composer.json needs to reflect that by setting the
minimum-stability to "alpha" or "dev"

    "minimum-stability": "alpha"

Install this bundle as usual by adding to composer.json:

    "oh/form-error-log-bundle": "dev-master"

Register the bundle in `app/AppKernel.php`:

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Oh\FormErrorLogBundle\OhFormErrorLogBundle(),
        );
    }

Set up
------------

There are 2 logging methods provided. One uses your normal logger (Monolog) and 
the other logs into a database

Method 1: Monolog
------------

You will need to create a new channel in your monolog settings called 'formerror'

    #app/config/config_prod.yml
	monolog:
		handlers:
			main:
				type:         fingers_crossed
				action_level: error
				handler:      nested
			nested:
				type:  stream
				path:  %kernel.logs_dir%/%kernel.environment%.log
				level: debug
			formerror:
				type:  stream
				path:  %kernel.logs_dir%/form-error-%kernel.environment%.log
				channels: formerror

Method 2: Database
------------

This uses Doctrine. You should create your own Entity which implements 
FormErrorLogEntityInterface

	<?php

	namespace Your\Bundle\Entity;

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

		
	}

You can create your own methods to store the date (I use Gedmo)

In your parameters.yml you can set the class to your entity

    #app/config/parameters.yml
    oh_form_error_log.db.entity.class: Your\Bundle\Entity\FormErrorLog


Your Form
-----------

Insert the listener into your form class:

	#YourBundle/Form/YourEntityType.php
	<?php

	namespace Your\Bundle\Form;

	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\OptionsResolver\OptionsResolverInterface;

	class YourEntityType extends AbstractType
	{
		public function buildForm(FormBuilderInterface $builder, array $options)
		{		
			if($options['logger']) {
				$builder->addEventSubscriber($options['logger']);
			}
		}

		public function setDefaultOptions(OptionsResolverInterface $resolver)
		{
			$resolver->setDefaults(array(
				'data_class' => 'Your\Bundle\Entity\YourEntity',
				'logger'=>false
			));
		}

		public function getName()
		{
			return 'your_bundle_yourentity';
		}
	}

And in your controller

	<?php

	namespace Your\Bundle\Controller;

	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Your\Bundle\Entity\YourEntityType;

	class YourController extends Controller
	{

		public function createAction()
		{

			$form = $this->createForm(new YourEntityType(), $entity, array(
					'logger'=>$this->get('oh_form_error_log.listener'))
					// or for the database version
					//'logger'=>$this->get('oh_form_error_log.listener.db'))
				);
			
			if ($form->isValid()) {
				// do stuff
			}
			
			return array(
				'form' => $form->createView(),
			);
		}
	}


Todo
-------

* Tests
* Support for Symfony\Component\Serializer\Normalizer
* Test with different FormTypes (like FileType)


Credits
-------

* Ollie Harridge (ollietb) as the author.