<?php
// src/Wyg/WygBundle/Entity/Meetup.php

namespace Wyg\WygBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Wyg\WygBundle\Repository\MeetupRepository")
 * @ORM\Table(name="meetup")
 * @ORM\HasLifecycleCallbacks()
 */
class Meetup
{
    public function __construct()
    {
        $this->setDtCreated(new \DateTime());
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length="50")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="id")
     */
    protected $owner;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dt_created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dt_meetup;

    /**
     * @ORM\Column(type="integer")
     */
    protected $duration;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $private;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="decimal", precision="18", scale="12")
     */
    protected $geo_long;

    /**
     * @ORM\Column(type="decimal", precision="18", scale="12")
     */
    protected $geo_lat;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="meetups")
     * @ORM\JoinTable(name="meetup_attendees")
     */
    protected $attendees;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set initiator
     *
     * @param string $initiator
     */
    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;
    }

    /**
     * Get initiator
     *
     * @return string 
     */
    public function getInitiator()
    {
        return $this->initiator;
    }

    /**
     * Set dt_created
     *
     * @param datetime $dtCreated
     */
    public function setDtCreated($dtCreated)
    {
        $this->dt_created = $dtCreated;
    }

    /**
     * Get dt_created
     *
     * @return datetime 
     */
    public function getDtCreated()
    {
        return $this->dt_created;
    }

    /**
     * Set dt_meetup
     *
     * @param datetime $dtMeetup
     */
    public function setDtMeetup($dtMeetup)
    {
        $this->dt_meetup = $dtMeetup;
    }

    /**
     * Get dt_meetup
     *
     * @return datetime 
     */
    public function getDtMeetup()
    {
        return $this->dt_meetup;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * Get duration
     *
     * @return integer 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set private
     *
     * @param boolean $private
     */
    public function setPrivate($private)
    {
        $this->private = $private;
    }

    /**
     * Get private
     *
     * @return boolean 
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set event_id
     *
     * @param integer $eventId
     */
    public function setEventId($eventId)
    {
        $this->event_id = $eventId;
    }

    /**
     * Get event_id
     *
     * @return integer 
     */
    public function getEventId()
    {
        return $this->event_id;
    }


    /**
     * Set geo_long
     *
     * @param decimal $geoLong
     */
    public function setGeoLong($geoLong)
    {
        $this->geo_long = $geoLong;
    }

    /**
     * Get geo_long
     *
     * @return decimal 
     */
    public function getGeoLong()
    {
        return $this->geo_long;
    }

    /**
     * Set geo_lat
     *
     * @param decimal $geoLat
     */
    public function setGeoLat($geoLat)
    {
        $this->geo_lat = $geoLat;
    }

    /**
     * Get geo_lat
     *
     * @return decimal 
     */
    public function getGeoLat()
    {
        return $this->geo_lat;
    }

    /**
     * Set owner_id
     *
     * @param Wyg\WygBundle\Entity\User $owner
     */
    public function setOwner(\Wyg\WygBundle\Entity\User $owner)
    {
        $this->owner_id = $owner->getId();
    }



    /**
     * Add users
     *
     * @param Wyg\WygBundle\Entity\User $user
     */
    public function addAttendee(\Wyg\WygBundle\Entity\User $user)
    {
        $this->attendees[] = $user;
    }

    /**
     * Get attendees
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAttendees()
    {
        return $this->attendees;
    }


    /**
     * Get owner
     *
     * @return Wyg\WygBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add attendees
     *
     * @param Wyg\WygBundle\Entity\User $attendees
     */
    public function addUser(\Wyg\WygBundle\Entity\User $attendees)
    {
        $this->attendees[] = $attendees;
    }
}