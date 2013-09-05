<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Tests\Adapter\Doctrine;

use Doctrine\Bundle\LicenseManagerBundle\Adapter\Doctrine\AuthorOrmRepository;
use Doctrine\Bundle\LicenseManagerBundle\Tests\Entity\AuthorRepositoryTest;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

class AuthorOrmRepositoryTest extends AuthorRepositoryTest
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    protected function createRepository()
    {
        return new AuthorOrmRepository($this->getEntityManager());
    }

    protected function commit()
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    protected function getEntityManager()
    {
        if ($this->entityManager === null) {
            $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . '/../../../Entity/'), true, null, null, false);

            $conn = array(
                'driver' => 'pdo_sqlite',
                'memory' => true,
            );

            $this->entityManager = EntityManager::create($conn, $config);

            $schemaTool = new SchemaTool($this->entityManager);
            $schemaTool->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
        }

        return $this->entityManager;
    }
}
