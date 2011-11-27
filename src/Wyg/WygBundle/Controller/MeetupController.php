<?php
// src/Wyg/WygBundle/Controller/MeetupController.php
namespace Wyg\WygBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wyg\WygBundle\Entity\Meetup;
use Wyg\WygBundle\Entity\User;
use Wyg\WygBundle\Form\MeetupType;


/**
 * Meetup controller.
 */
class MeetupController extends Controller
{
    /**
     * Show a meetup entry
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $meetup = $em->getRepository('WygWygBundle:Meetup')->find($id);
        if (!$meetup) {
            throw $this->createNotFoundException('Unable to find this meetup.');
        }

        return $this->render('WygWygBundle:Meetup:show.html.twig', array(
            'meetup'      => $meetup,
        ));
    }


    /**
     * Show all the meetups
     */
    public function showAllAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        // Just like a findAll() but with an orderBy()
        $meetups = $em->getRepository('WygWygBundle:Meetup')->findBy(array(), array('dt_meetup' => 'ASC'));;

        return $this->render('WygWygBundle:Meetup:showcollection.html.twig', array(
            'meetups'      => $meetups,
        ));
    }

    /**
     * Show all the meetups
     */
    public function showLatestAction($count)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $meetups = $em->getRepository('WygWygBundle:Meetup')->getLatest($count);

        return $this->render('WygWygBundle:Meetup:showcollection.html.twig', array(
            'meetups'      => $meetups,
        ));
    }

    public function newAction()
    {

        $meetup = new Meetup();
        $form = $this->createForm(new MeetupType(), $meetup);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $user = $this->get('security.context')->getToken()->getUser();
                $meetup->setOwnerId($user);
                $meetup->addAttendee($user);

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($meetup);
                $em->flush();

                $this->get('session')->setFlash('notice', 'Your meetup is created!');

                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                return $this->redirect($this->generateUrl('WygWygBundle_homepage'));
            }
        }

        return $this->render('WygWygBundle:Meetup:form.html.twig', array(
            'form' => $form->createView()
        ));
    }

}