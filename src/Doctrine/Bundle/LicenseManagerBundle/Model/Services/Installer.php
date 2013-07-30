<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Model\Services;

use Doctrine\Bundle\LicenseManagerBundle\Entity\License;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

class Installer
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function install()
    {
        $this->loadLicenses();
    }

    private function loadLicenses()
    {
        $dql = 'SELECT l FROM Doctrine\Bundle\LicenseManagerBundle\Entity\License l INDEX BY l.spdxIdentifier';
        $query = $this->entityManager->createQuery($dql);
        $licenses = $query->getResult();

        $url = "http://spdx.org/licenses/";
        $content = file_get_contents($url);

        $crawler = new Crawler($content);
        $rows = $crawler->filter('table tbody tr');

        foreach ($rows as $row) {
            $row = new Crawler($row);
            $tds = $row->filter('td');

            $isOSIApproved = $tds->eq(2)->text() === "Y";

            if (!$isOSIApproved) {
                continue;
            }

            $identifier = trim($tds->eq(1)->text());

            if (isset($licenses[$identifier])) {
                continue;
            }

            $name = $tds->eq(0)->text();
            $licenseUrl = $url . substr($tds->eq(3)->filter('a')->attr('href'), 2);

            $license = new License($name, $licenseUrl, $identifier);
            $this->entityManager->persist($license);
        }

        $this->entityManager->flush();
    }
}
