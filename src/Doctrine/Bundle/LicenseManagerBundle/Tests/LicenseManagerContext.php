<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Tests;

use Doctrine\Bundle\LicenseManagerBundle\Entity\Author;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Project;

class LicenseManagerContext
{
    /**
     * @return Author
     */
    public function createAuthor()
    {
        return new Author(
            'johndoe',
            'john.doe@example.com',
            $this->createProject()
        );
    }

    /**
     * @return Project
     */
    public function createProject()
    {
        $project = new Project('foo');
        $project->setEmailMessage('');
        $project->setPageMessage('');
        $project->setSender('', '');

        return $project;
    }
}
