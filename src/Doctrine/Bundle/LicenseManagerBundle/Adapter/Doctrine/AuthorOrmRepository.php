<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Adapter\Doctrine;

use Doctrine\ORM\EntityManager;

use Doctrine\Bundle\LicenseManagerBundle\Entity\AuthorNotFoundException;
use Doctrine\Bundle\LicenseManagerBundle\Entity\AuthorRepository;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Author;

class AuthorOrmRepository implements AuthorRepository
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function find($id)
    {
        $author = $this->entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);

        if (!$author) {
            throw new AuthorNotFoundException($id);
        }

        return $author;
    }

    public function add(Author $author)
    {
        $this->entityManager->persist($author);
        $this->entityManager->persist($author->getProject());
    }
}
