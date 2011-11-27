<?php
// src/Wyg/WygBundle/Form/EnquiryType.php

namespace Wyg\WygBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('username');

        $builder->add('firstname');
        $builder->add('lastname');

        $builder->add('email', 'email');

        $builder->add('password', 'repeated', array (
            'type'            => 'password',
            'first_name'      => "Password",
            'second_name'     => "Re-enter Password",
            'invalid_message' => "The passwords don't match!",
//            'options'         => array('required' => false),
        ));

    }

    public function getName()
    {
        return 'user';
    }
}