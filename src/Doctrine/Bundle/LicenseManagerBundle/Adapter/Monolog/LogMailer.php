<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Adapter\Monolog;

use Monolog\Logger;
use Doctrine\Bundle\LicenseManagerBundle\Model\Services\Mailer;

class LogMailer implements Mailer
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function sendTextMessage($sender, $receiver, $subject, $message)
    {
        $this->logger->info(sprintf("Sending '%s' from %s to %s", $subject, $sender, $receiver), array('message' => $message));
    }
}
