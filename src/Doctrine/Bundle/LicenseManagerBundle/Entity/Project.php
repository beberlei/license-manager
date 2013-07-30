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

    /** @ORM\Column(type="text") */
    protected $emailMessage;

    /**
     * @ORM\Column(type="text")
     */
    protected $pageMessage;

    /**
     * @ORM\ManyToOne(targetEntity="License")
     */
    protected $fromLicense;

    /**
     * @ORM\ManyToOne(targetEntity="License")
     */
    protected $toLicense;

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
     * @return bool
     */
    public function confirmed()
    {
        return $this->confirmed;
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

    public function setEmailMessage($text)
    {
        $this->emailMessage = $text;
    }

    /**
     * @return string
     */
    public function getEmailMessage()
    {
        return $this->emailMessage;
    }

    public function getPageMessage()
    {
        return $this->pageMessage;
    }

    public function setPageMessage($pageMessage)
    {
        $this->pageMessage = $pageMessage;
    }

    /**
     * Get toLicense.
     *
     * @return License
     */
    public function getToLicense()
    {
        return $this->toLicense;
    }

    public function setToLicense(License $toLicense)
    {
        $this->toLicense = $toLicense;
    }

    /**
     * Get fromLicense.
     *
     * @return License
     */
    public function getFromLicense()
    {
        return $this->fromLicense;
    }

    public function setFromLicense(License $fromLicense)
    {
        $this->fromLicense = $fromLicense;
    }
}

