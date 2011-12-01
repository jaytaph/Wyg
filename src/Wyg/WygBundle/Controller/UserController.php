<?php
// src/Wyg/WygBundle/Controller/UserController.php

namespace Wyg\WygBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Wyg\WygBundle\Entity\User;
use Wyg\WygBundle\Entity\Betakey;
use Wyg\WygBundle\Form\UserType;
use Wyg\WygBundle\Form\UserBetaKeyType;

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
                $betakey = $em->getRepository('WygWygBundle:Betakey')->findOneByBetakey($formkey);
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
            $user = $em->getRepository('WygWygBundle:User')->findOneByActivationKey($key);
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
                    ->setBody($this->renderView('WygWygBundle:User:newAccountActivatedEmail.txt.twig', array('user' => $user, 'newkeys' => $newkeys)));
                $this->get('mailer')->send($message);

                $this->get('session')->setFlash('notice', 'Your profile has been activated!');
                return $this->redirect($this->generateUrl('WygWygBundle_homepage'));

            }
        }
        return $this->render('WygWygBundle:User:activate.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Let a user create a new account
     */
    public function forgotAction()
    {
        $form = $this->createFormBuilder()
                    ->add('email', 'email')
                    ->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $em = $this->getDoctrine()->getEntityManager();
                $user = $em->getRepository('WygWygBundle:User')->findOneByEmail($data['email']);
                if ($user) {
                    // Generate key
                    $user->createForgotPasswordConfirmationKey();
                    $em = $this->getDoctrine()->getEntityManager();
                    $em->persist($user);
                    $em->flush();

                    // Email user with confirmation key
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Forgot your Wyg Password?')
                        ->setFrom('wyg@noxlogic.nl')
                        ->setTo($user->getEmail())
                        ->setBody($this->renderView('WygWygBundle:User:forgotPasswordStage1Email.txt.twig', array('user' => $user)));
                    $this->get('mailer')->send($message);
                }

                // ALWAYS display a notice on screen
                $this->get('session')->setFlash('notice', 'Confirmation email has been send');

                // Redirect to index
                return $this->redirect($this->generateUrl('WygWygBundle_user_forgot'));
            }
        }

        return $this->render('WygWygBundle:User:forgot.html.twig', array(
            'form' => $form->createView(),
            'mode'      => 'add',
        ));
    }


    /**
     *
     */
    public function forgotConfirmAction($email, $key) {
        $key = $this->getRequest()->get('key');
        if (! $key) {
            return $this->redirect($this->generateUrl('WygWygBundle_user_forgot'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('WygWygBundle:User')->findOneByEmail($email);
        if (!$user || $user->getForgotPassKey() != $key) {
            $this->get('session')->setFlash('notice', 'This key is not valid');
            return $this->redirect($this->generateUrl('WygWygBundle_user_forgot'));
        }

        $cleartext_password = $user->generatePassword();
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($cleartext_password, $user->getSalt());
        $user->setPassword($password);
        $em->persist($user);
        $em->flush();

        // Email user
        $message = \Swift_Message::newInstance()
            ->setSubject('Your Wyg password has been reset')
            ->setFrom('wyg@noxlogic.nl')
            ->setTo($user->getEmail())
            ->setBody($this->renderView('WygWygBundle:User:forgotPasswordStage2Email.txt.twig', array('user' => $user, 'password' => $cleartext_password)));
        $this->get('mailer')->send($message);

        $this->get('session')->setFlash('notice', 'Your password has been reset!');
        return $this->redirect($this->generateUrl('WygSecurityBundle_login'));
    }


}