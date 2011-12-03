<?php
// src/Wyg/MeetupBundle/Controller/MeetupController.php
namespace Wyg\MeetupBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Wyg\MeetupBundle\Entity\Meetup;
//use Wyg\UserBundle\Entity\User;
use Wyg\MeetupBundle\Form\MeetupType;
use Ivory\GoogleMapBundle\Model\MapTypeId;
use Ivory\GoogleMapBundle\Model\Overlays\Animation;

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
        $meetup = $em->getRepository('WygMeetupBundle:Meetup')->find($id);
        if (!$meetup) {
            throw $this->createNotFoundException('Unable to find this meetup.');
        }


        // Requests the ivory google map marker service
        $marker = $this->get('ivory_google_map.marker');

        // Configure your marker options
        $marker->setPrefixJavascriptVariable('marker_');
        $marker->setPosition($meetup->getGeoLat(), $meetup->getGeoLong(), true);
        $marker->setAnimation(Animation::DROP);

        $marker->setOption('clickable', true);
        $marker->setOption('flat', true);



        // Requests the ivory google map service
        $map = $this->get('ivory_google_map.map');
        $map->setPrefixJavascriptVariable('map_');
        $map->setHtmlContainerId('map_canvas');
        $map->setCenter($meetup->getGeoLat(), $meetup->getGeoLong(), true);
        $map->setMapOption('zoom', 16);
        $map->setMapOption('mapTypeId', MapTypeId::HYBRID);
        $map->setStylesheetOptions(array('width' => '300px', 'height' => '300px'));

        $map->addMarker($marker);

        return $this->render('WygMeetupBundle:Meetup:show.html.twig', array(
            'meetup'      => $meetup,
            'map'         => $map,
        ));
    }


    /**
     * Show all the meetups
     */
    public function showAllAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        // Just like a findAll() but with an orderBy()
        $meetups = $em->getRepository('WygMeetupBundle:Meetup')->findBy(array(), array('dt_meetup' => 'ASC'));;

        return $this->render('WygMeetupBundle:Meetup:showcollection.html.twig', array(
            'meetups'      => $meetups,
        ));
    }

    /**
     * Show all the meetups
     */
    public function showLatestAction($count)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $meetups = $em->getRepository('WygMeetupBundle:Meetup')->getLatest($count);

        return $this->render('WygMeetupBundle:Meetup:showcollection.html.twig', array(
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
                return $this->redirect($this->generateUrl('WygUserBundle_homepage'));
            }
        }

        return $this->render('WygMeetupBundle:Meetup:form.html.twig', array(
            'form' => $form->createView()
        ));
    }


    public function attendAction($id, $status)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $meetup = $em->getRepository('WygMeetupBundle:Meetup')->find($id);
        if (!$meetup) {
            throw $this->createNotFoundException('Unable to find this meetup.');
        }

        if ($status != "yes" and $status != "no") {
            throw new \OutOfBoundsException("Either use 'yes' or 'no' as status");
        }

        $user = $this->get('security.context')->getToken()->getUser();

        // Set status to attending or not
        if ($status == "yes") {
            $meetup->addAttendee($user);
            $notice = 'You have been added to the meetup';
        } else {
            $meetup->removeAttendee($user);
            $notice = 'You have been removed from the meetup';
        }
        $em->persist($meetup);
        $em->flush();


        $this->get('session')->setFlash('notice', $notice);
        return $this->redirect($this->generateUrl('WygMeetupBundle_meetup_show', array('id' => $meetup->getId())));
    }

}