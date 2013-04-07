<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/licenses/projects');

        $content = $client->getResponse()->getContent();
        $this->assertContains("Name", $content);
        $this->assertContains("Confirmed Insertions %", $content);
        $this->assertContains("Trival/Removed Code %", $content);
    }
}
