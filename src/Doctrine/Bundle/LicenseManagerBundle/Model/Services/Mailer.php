<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Model\Services;

interface Mailer
{
    /**
     * @param string $sender
     * @param string $receiver
     * @param string $subject
     * @param string $message
     */
    public function sendTextMessage($sender, $receiver, $subject, $message);
}
