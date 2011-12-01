<?php
// src/Wyg/WygBundle/Form/EnquiryType.php

namespace Wyg\WygBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserBetaKeyType extends UserType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('betakey', 'text', array('property_path' => false));

//        $builder->
//                addValidator(new CallbackValidator(function(FormInterface $form)
//                {
//                    if (!$form["t_and_c"]->getData())
//                    {
//                        $form->addError(new FormError('Please accept the terms and conditions in order to register'));
//                    }
//                })
//            );

        parent::buildForm($builder, $options);
    }


    public function getName()
    {
        return 'betakey';
    }
}