<?php
// src/Wyg/UserBundle/Controller/UserController.php

namespace Wyg\UserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Wyg\UserBundle\Entity\User;
use Wyg\UserBundle\Entity\Betakey;
use Wyg\UserBundle\Form\UserType;
use Wyg\UserBundle\Form\UserBetaKeyType;

/**
 * User controller.
 */
class UserController extends Controller
{

    /**
     * Displays the current logged in profile
     */
    public function profileAction()
    {
        // Fetch the current logged in user. It's always present, since the firewall denies anonymous logins
        $user = $this->get('security.context')->getToken()->getUser();

        // Create form
        $form = $this->createForm(new UserType(), $user);
        $form->remove('username');

//        //@TODO We must make sure that the password fields are optional!
//        foreach ($form->get('password')->getChildren() as $item) {
//            $item->setRequired(true);
//        }

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($password);

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($user);
                $em->flush();

                $this->get('session')->setFlash('notice', 'Your profile has been updated!');

                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                return $this->redirect($this->generateUrl('WygUserBundle_user_profile'));
            }
        }

        return $this->render('WygUserBundle:User:form.html.twig', array(
            'form' => $form->createView(),
            'user'      => $user,
            'mode'      => 'edit',
        ));
    }

}