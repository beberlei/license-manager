<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Adapter\Doctrine;

use Doctrine\ORM\EntityManager;

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
        $author = $this->entityManager->find('Doctrine\Bundle\LicenseManager\Entity\Author', $id);

        if (!$author) {
            throw new \RuntimeException();
        }

        return $author;
    }

    public function add(Author $author)
    {
        $this->entityManager->persist($author);
    }
}
