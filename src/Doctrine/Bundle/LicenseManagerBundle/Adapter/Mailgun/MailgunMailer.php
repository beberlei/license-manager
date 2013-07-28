<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Adapter\Mailgun;

use Doctrine\Bundle\LicenseManagerBundle\Model\Services\Mailer;

class MailgunMailer implements Mailer
{
    private $client;
    private $headers;
    private $domain;

    public function __construct($apiKey, $domain)
    {
        $this->client = new \Buzz\Browser(new \Buzz\Client\Curl);
        $this->headers = array('Authorization: Basic ' . base64_encode($apiKey));
        $this->domain = $domain;
    }

    public function sendTextMessage($sender, $receiver, $subject, $message)
    {
        $content = http_build_query(array(
            'from'    => $sender,
            'to'      => $receiver,
            'subject' => $subject,
            'text'    => $message
        ));

        $this->client->post('https://api.mailgun.net/v2/' . $this->domain . '/messages', $this->headers, $content);
    }
}
