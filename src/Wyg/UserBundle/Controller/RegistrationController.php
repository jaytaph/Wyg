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
class RegistrationController extends Controller
{


    /**
     * Let a user create a new account
     */
    public function registerAction()
    {
        $user = new User();


        $useBetaKeys = $this->container->getParameter('registration.betakeys.active');
        if ($useBetaKeys) {
            $form = $this->createForm(new UserBetaKeyType(), $user);
        } else {
            $form = $this->createForm(new UserType(), $user);
        }



        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            $em = $this->getDoctrine()->getEntityManager();
            if ($useBetaKeys) {
                // Check beta keys
                $formkey = $form->get('betakey')->getData();
                $betakey = $em->getRepository('WygUserBundle:Betakey')->findOneByBetakey($formkey);
                if (! $betakey) {
                    $form->addError(new FormError('This beta key is not found, or already used.'));
                } else {
                    $em->remove($betakey);
                    $em->flush();
                }
            }


            if ($form->isValid()) {
                // Hash password for user
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($password);

                // Save user
                $em->persist($user);
                $em->flush();

                // Email user with confirmation key
                $message = \Swift_Message::newInstance()
                    ->setSubject('Your new account at Wyg')
                    ->setFrom('wyg@noxlogic.nl')
                    ->setTo($user->getEmail())
                    ->setBody($this->renderView('WygUserBundle:Registration:newAccountEmail.txt.twig', array('user' => $user)));
                $this->get('mailer')->send($message);

                // Notice on screen
                $this->get('session')->setFlash('notice', 'Your profile has been created. Watch your email for your activation key!');

                // Redirect to index
                return $this->redirect($this->generateUrl('WygUserBundle_user_activate'));
            }
        }

        return $this->render('WygUserBundle:Registration:form.html.twig', array(
            'form' => $form->createView(),
            'mode'      => 'add',
        ));
    }

    /**
     * Confirms a new account by its activate key
     */
    public function activateAction() {
        // Dynamically create a activation form
        $form = $this->createFormBuilder()
                    ->add('activationkey', 'text')
                    ->getForm();

        $request = $this->getRequest();

        // If the key is received from a GET request, we add the key data to the form. This way, the user only
        // has to press the submit button.
        if ($request->getMethod() == 'GET' && $request->get('key')) {
            $form->setData(array('activationkey' => $request->get('key')));
        }

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            $em = $this->getDoctrine()->getEntityManager();
            $key = $form->get('activationkey')->getData();
            $user = $em->getRepository('WygUserBundle:User')->findOneByActivationKey($key);
            if (!$user) {
                $form->addError(new FormError('This activation key is not found, or already used.'));
            }

            if ($form->isValid()) {
                // Activate the user and save
                $user->activate();
                $em->persist($user);
                $em->flush();

                // Add an amount of new betakeys if needed
                $newkeys = array();
                $useBetaKeys = $this->container->getParameter('registration.betakeys.active');
                if ($useBetaKeys) {
                    $respawn = $this->container->getParameter('registration.betakeys.respawn');
                    for ($i=0; $i<$respawn; $i++) {
                        $betakey = new Betakey();
                        $newkeys[] = $betakey;
                        $em->persist($betakey);
                        $em->flush();
                    }
                }

                // Email user
                $message = \Swift_Message::newInstance()
                    ->setSubject('Your new account at Wyg')
                    ->setFrom('wyg@noxlogic.nl')
                    ->setTo($user->getEmail())
                    ->setBody($this->renderView('WygUserBundle:Registration:newAccountActivatedEmail.txt.twig', array('user' => $user, 'newkeys' => $newkeys)));
                $this->get('mailer')->send($message);

                $this->get('session')->setFlash('notice', 'Your profile has been activated!');
                return $this->redirect($this->generateUrl('WygUserBundle_homepage'));

            }
        }
        return $this->render('WygUserBundle:Registration:activate.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}