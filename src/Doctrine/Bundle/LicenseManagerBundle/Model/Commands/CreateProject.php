<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Model\Commands;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProject
{
    /**
     * @Assert\NotBlank
     */
    public $name;
    /**
     * @Assert\NotBlank
     * @Assert\Url
     */
    public $githubUrl;

    /**
     * @Assert\NotBlank
     */
    public $pageMessage;

    /**
     * @Assert\NotBlank
     */
    public $emailMessage;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     */
    public $fromLicense;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     */
    public $toLicense;
}
