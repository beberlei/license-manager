<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="project")
 */
class Project
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    protected $id;
    /** @ORM\Column */
    protected $name;
    /** @ORM\Column(unique=true) */
    protected $githubUrl;
    /** @ORM\Column(type="boolean") */
    protected $confirmed = false;

    public function __construct($name, $url)
    {
        $this->name      = $name;
        $this->githubUrl = $url;
    }

    public function markConfirmed()
    {
        $this->confirmed = true;
    }

    /**
     * Get id.
     *
     * @return id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param name the value to set.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get githubUrl.
     *
     * @return githubUrl.
     */
    public function getGithubUrl()
    {
        return $this->githubUrl;
    }

    /**
     * Set githubUrl.
     *
     * @param githubUrl the value to set.
     */
    public function setGithubUrl($githubUrl)
    {
        $this->githubUrl = $githubUrl;
    }
}

