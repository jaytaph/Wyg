<?php
// src/Wyg/WygBundle/Entity/Betakey.php

namespace Wyg\WygBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Wyg\WygBundle\Repository\BetakeyRepository")
 * @ORM\Table(name="betakeys")
 */
class Betakey
{
    public function __construct()
    {
        $this->setBetaKey(md5(microtime() . mt_rand(0, mt_getrandmax())));
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length="50")
     */
    protected $betakey;

    /**
     * Set betakey
     *
     * @param string $betakey
     */
    public function setBetakey($betakey)
    {
        $this->betakey = $betakey;
    }

    /**
     * Get betakey
     *
     * @return string 
     */
    public function getBetakey()
    {
        return $this->betakey;
    }
}