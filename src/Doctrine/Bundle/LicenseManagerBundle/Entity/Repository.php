<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="repository")
 */
class Repository
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    protected $id;

    /** @ORM\ManyToOne(targetEntity="Project", inversedBy="repositories") */
    protected $project;

    /** @ORM\Column(unique=true) */
    protected $url;

    public function __construct(Project $project, $url)
    {
        $this->project = $project;
        $this->url = $url;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
