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
class ForgotPasswordController extends Controller
{

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
                $user = $em->getRepository('WygUserBundle:User')->findOneByEmail($data['email']);
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
                        ->setBody($this->renderView('WygUserBundle:ForgotPassword:forgotPasswordStage1Email.txt.twig', array('user' => $user)));
                    $this->get('mailer')->send($message);
                }

                // ALWAYS display a notice on screen
                $this->get('session')->setFlash('notice', 'Confirmation email has been send');

                // Redirect to index
                return $this->redirect($this->generateUrl('WygUserBundle_user_forgot'));
            }
        }

        return $this->render('WygUserBundle:ForgotPassword:forgot.html.twig', array(
            'form' => $form->createView(),
            'mode'      => 'add',
        ));
    }


    /**
     *
     */
    public function confirmAction($email, $key) {
        $key = $this->getRequest()->get('key');
        if (! $key) {
            return $this->redirect($this->generateUrl('WygUserBundle_user_forgot'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('WygUserBundle:User')->findOneByEmail($email);
        if (!$user || $user->getForgotPassKey() != $key) {
            $this->get('session')->setFlash('notice', 'This key is not valid');
            return $this->redirect($this->generateUrl('WygUserBundle_user_forgot'));
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
            ->setBody($this->renderView('WygUserBundle:ForgotPassword:forgotPasswordStage2Email.txt.twig', array('user' => $user, 'password' => $cleartext_password)));
        $this->get('mailer')->send($message);

        $this->get('session')->setFlash('notice', 'Your password has been reset!');
        return $this->redirect($this->generateUrl('WygSecurityBundle_login'));
    }


}