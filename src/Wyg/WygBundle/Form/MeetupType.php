<?php
// src/Wyg/WygBundle/Form/EnquiryType.php

namespace Wyg\WygBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MeetupType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name');
        $builder->add('description', 'textarea');

        $builder->add('geo_long');
        $builder->add('geo_lat');

        $builder->add('dt_meetup', 'datetime');
        $builder->add('duration', 'number');

        $builder->add('private', 'checkbox');
    }

    public function getName()
    {
        return 'meetup';
    }
}