<?php
namespace Wyg\DefaultBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wyg\UserBundle\Entity\Enquiry;
use Wyg\UserBundle\Form\EnquiryType;


class PageController extends Controller
{
    public function indexAction()
    {
        return $this->render('WygDefaultBundle:Page:index.html.twig');
    }

    public function aboutAction()
    {
        return $this->render('WygDefaultBundle:Page:about.html.twig');
    }

    public function contactAction()
    {
        $enquiry = new Enquiry();
        $form = $this->createForm(new EnquiryType(), $enquiry);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {

                   $message = \Swift_Message::newInstance()
                       ->setSubject('Contact enquiry from Wyg')
                       ->setFrom('enquiries@noxlogic.nl')
                       ->setTo($this->container->getParameter('wyg_wyg.emails.contact_email'))
                       ->setBody($this->renderView('WygDefaultBundle:Page:contactEmail.txt.twig', array('enquiry' => $enquiry)));
                   $this->get('mailer')->send($message);

                   $this->get('session')->setFlash('notice', 'Your contact enquiry was successfully sent. Thank you!');

                   // Redirect - This is important to prevent users re-posting
                   // the form if they refresh the page
                   return $this->redirect($this->generateUrl('WygDefaultBundle_contact'));
               }
        }

        return $this->render('WygDefaultBundle:Page:contact.html.twig', array(
            'form' => $form->createView()
        ));
    }

}