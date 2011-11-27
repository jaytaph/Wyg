<?php
// src/Wyg/WygBundle/Controller/UserController.php

namespace Wyg\WygBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Wyg\WygBundle\Entity\User;
use Wyg\WygBundle\Form\UserType;

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

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($form->getData());
                $em->flush();

                $this->get('session')->setFlash('notice', 'Your profile has been updated!');

                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                return $this->redirect($this->generateUrl('WygWygBundle_user_profile'));
            }
        }

        return $this->render('WygWygBundle:User:form.html.twig', array(
            'form' => $form->createView(),
            'user'      => $user,
            'mode'      => 'edit',
        ));
    }

    /**
     * Let a user create a new account
     */
    public function registerAction()
    {
        $user = new User();

        $form = $this->createForm(new UserType(), $user);
        //@TODO We must make sure that the password fields are optional!
//        foreach ($form->get('password')->getChildren() as $item) {
//            $item->setRequired(true);
//        }

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                // Store the user
                $user = $form->getData();

                // Check if user exists
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($user);
                $em->flush();

                // Email user with confirmation key
                $message = \Swift_Message::newInstance()
                    ->setSubject('Your new account at Wyg')
                    ->setFrom('wyg@noxlogic.nl')
                    ->setTo($user->getEmail())
                    ->setBody($this->renderView('WygWygBundle:User:newAccountEmail.txt.twig', array('user' => $user)));
                $this->get('mailer')->send($message);

                // Notice on screen
                $this->get('session')->setFlash('notice', 'Your profile has been created. Watch your email for your activation key!');

                // Redirect to index
                return $this->redirect($this->generateUrl('WygWygBundle_user_activate'));
            }
        }

        return $this->render('WygWygBundle:User:form.html.twig', array(
            'form' => $form->createView(),
            'mode'      => 'add',
        ));
    }

    /**
     * Confirms a new account by its activate key
     */
    public function activateAction() {

        $form = $this->createFormBuilder()
                    ->add('activationkey', 'text')
                    ->getForm();

        $request = $this->getRequest();

        if ($request->getMethod() == 'GET' && $request->get('key')) {
            $form->setData(array('activationkey' => $request->get('key')));
        }

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                $em = $this->getDoctrine()->getEntityManager();
                $user = $em->getRepository('WygWygBundle:User')->findOneByActivationKey($data['activationkey']);
                if (!$user) {
                    $form->addError(new FormError('This activation key is not found, or already used.'));
                } else {

                    $user->activate();
                    $em->persist($user);
                    $em->flush();

                    // Email user
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Your new account at Wyg')
                        ->setFrom('wyg@noxlogic.nl')
                        ->setTo($user->getEmail())
                        ->setBody($this->renderView('WygWygBundle:User:newAccountActivatedEmail.txt.twig', array('user' => $user)));
                    $this->get('mailer')->send($message);

                    $this->get('session')->setFlash('notice', 'Your profile has been activated!');
                    return $this->redirect($this->generateUrl('WygWygBundle_homepage'));
                }

            }
        }
        return $this->render('WygWygBundle:User:activate.html.twig', array(
            'form' => $form->createView(),
        ));

//
//        // Email user
//        $message = \Swift_Message::newInstance()
//            ->setSubject('Your new account at Wyg')
//            ->setFrom('wyg@noxlogic.nl')
//            ->setTo($user->getEmail())
//            ->setBody($this->renderView('WygWygBundle:User:newAccountActivatedEmail.txt.twig', array('user' => $user)));
//        $this->get('mailer')->send($message);
//
//        $this->get('session')->setFlash('notice', 'Your profile has been activated!');
//
//        // Redirect - This is important to prevent users re-posting
//        // the form if they refresh the page
//        return $this->redirect($this->generateUrl('WygWygBundle_homepage'));
    }

}