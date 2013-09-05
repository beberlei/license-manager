<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Tests\Entity;

use Doctrine\Bundle\LicenseManagerBundle\Tests\LicenseManagerContext;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Author;

abstract class AuthorRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_finds_authors()
    {
        $context = new LicenseManagerContext();
        $newAuthor = $context->createAuthor();

        $repository = $this->createRepository();
        $repository->add($newAuthor);

        $this->commit();

        $newRepository = $this->createRepository();
        $persistentAuthor = $newRepository->find($newAuthor->getId());

        $this->assertInstanceOf(
            'Doctrine\Bundle\LicenseManagerBundle\Entity\Author',
            $persistentAuthor
        );

        $this->assertEquals($newAuthor->getId(), $persistentAuthor->getId());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_author_not_found()
    {
        $this->setExpectedException('Doctrine\Bundle\LicenseManagerBundle\Entity\AuthorNotFoundException');

        $repository = $this->createRepository();
        $repository->find(987654321);
    }

    /**
     * @return AuthorRepository
     */
    abstract protected function createRepository();

    /**
     * @return void
     */
    abstract protected function commit();
}
