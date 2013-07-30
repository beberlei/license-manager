<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="license")
 */
class License
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $url;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $spdxIdentifier;

    public function __construct($name, $url, $spdxIdentifier)
    {
        $this->name = $name;
        $this->url = $url;
        $this->spdxIdentifier = $spdxIdentifier;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getSpdxIdentifier()
    {
        return $this->spdxIdentifier;
    }
}

