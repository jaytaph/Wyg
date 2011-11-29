<?php
// src/Wyg/WygBundle/Entity/User.php

namespace Wyg\WygBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

// @TODO: use advancedInterface: http://readthedocs.org/docs/test-sf-doc-es/en/latest/book/security/users.html

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks()
 * @DoctrineAssert\UniqueEntity(
 *     fields = { "username" }
 * )
 */
class User implements UserInterface
{
    public function __construct()
    {
        $this->setDtCreated(new \DateTime());
        $this->setSalt(md5(microtime() . rand(1,getrandmax())));
        $this->setActivationKey(md5(microtime() . rand(1,getrandmax())));
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
    protected $username;

    /**
     * @ORM\Column(type="string", length="50")
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length="50")
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string", length="50")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length="50")
     */
    protected $salt;

    /**
     * @ORM\Column(name="activation_key", type="string", length="50", nullable=true)
     */
    protected $activationKey;

    /**
     * @ORM\Column(name="forgotpass_key", type="string", length="50", nullable=true)
     */
    protected $forgotPassKey;


    /**
     * @ORM\ManyToMany(targetEntity="Wyg\SecurityBundle\Entity\Role")
     * @ORM\JoinTable(name="user_role",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $userRoles
     */
    protected $userRoles;

    /**
     * @ORM\Column(type="string", length="50")
     */
    protected $email;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dt_created;

    /**
     * @ORM\OneToMany(targetEntity="Meetup", mappedBy="owner_id")
     */
    protected $meetups;


    /**
     * @ORM\OneToMany(targetEntity="Meetup", mappedBy="owner_id")
     */
    protected $owned_meetups;

    /**
     * @ORM\ManyToMany(targetEntity="Meetup", mappedBy="attendees")
     * @ORM\JoinTable(name="meetup_attendees")
     */
    protected $attended_meetups;


    public function getRoles()
    {
        return $this->getUserRoles()->toArray();
    }
    public function getUserRoles()
    {
        return $this->userRoles;
    }
    public function equals(UserInterface $user)
    {
        return md5($this->getUsername()) == md5($user->getUsername());
    }
    public function getSalt()
    {
        return $this->salt;
    }
    public function eraseCredentials()
    {
    }

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
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
//        if (empty($password)) return;
//
//        // @TODO: Not sure if this is the correct place (need @PrePersist?).
//        $factory = $this-> ->get('security.encoder_factory');
//        $encoder = $factory->getEncoder($this);
//        $this->password = $encoder->encodePassword($password, $this->getSalt());
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
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
     * Set is_admin
     *
     * @param boolean $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->is_admin = $isAdmin;
    }

    /**
     * Get is_admin
     *
     * @return boolean 
     */
    public function getIsAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Add meetups
     *
     * @param Wyg\WygBundle\Entity\Meetup $meetups
     */
    public function addMeetup(\Wyg\WygBundle\Entity\Meetup $meetups)
    {
        $this->meetups[] = $meetups;
    }

    /**
     * Get meetups
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getMeetups()
    {
        return $this->meetups;
    }

    /**
     * Get owned_meetups
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getOwnedMeetups()
    {
        return $this->owned_meetups;
    }

    /**
     * Get attended_meetups
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAttendedMeetups()
    {
        return $this->attended_meetups;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }



    /**
     * Add userRoles
     *
     * @param Wyg\SecurityBundle\Entity\Role $userRoles
     */
    public function addRole(\Wyg\SecurityBundle\Entity\Role $userRoles)
    {
        $this->userRoles[] = $userRoles;
    }

    public function isActivated() {
        $key = $this->getActivationKey();
        return empty($key);
    }

    public function activate() {
        $this->setActivationKey(null);
    }


    /**
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set activationKey
     *
     * @param string $activationKey
     */
    public function setActivationKey($activationKey)
    {
        $this->activationKey = $activationKey;
    }

    /**
     * Get activationKey
     *
     * @return string 
     */
    public function getActivationKey()
    {
        return $this->activationKey;
    }

    public function createForgotPasswordConfirmationKey() {
        $this->setForgotPassKey(md5(microtime() . rand(1,getrandmax())));
        return $this->getForgotPassKey();
    }


    /**
     * Set forgotPassKey
     *
     * @param string $forgotPassKey
     */
    public function setForgotPassKey($forgotPassKey)
    {
        $this->forgotPassKey = $forgotPassKey;
    }

    /**
     * Get forgotPassKey
     *
     * @return string 
     */
    public function getForgotPassKey()
    {
        return $this->forgotPassKey;
    }

    public function generatePassword() {
        $password = substr(md5(microtime() . rand(1,getrandmax())), 0, 12);
        $this->setPassword($password);
        return $password;
    }
}