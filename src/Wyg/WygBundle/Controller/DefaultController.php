<?php

namespace Wyg\WygBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('WygWygBundle:Default:index.html.twig', array('name' => $name));
    }
}
