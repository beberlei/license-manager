<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/licenses/projects');

        $this->assertTrue($crawler->filter('html:contains("Name")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Confirmed Insertions %")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Trival/Removed Code %")')->count() > 0);
    }
}
