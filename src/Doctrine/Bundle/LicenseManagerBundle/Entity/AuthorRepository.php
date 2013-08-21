<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Entity;

interface AuthorRepository
{
    /**
     * Find an author
     *
     * @throws AuthorNotFoundException
     * @return Author
     */
    public function find($id);

    /**
     * Add author to repository
     *
     * @param Author $author
     * @return void
     */
    public function add(Author $author);
}
