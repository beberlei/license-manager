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
     * @Assert\All({
     *     @Assert\NotBlank(),
     *     @Assert\Url()
     * })
     */
    public $url = array("");

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
     */
    public $senderName;

    /**
     * @Assert\NotBlank
     * @Assert\Email
     */
    public $senderMail;

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
