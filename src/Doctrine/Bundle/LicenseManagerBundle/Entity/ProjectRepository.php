<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Entity;

interface ProjectRepository
{
    /**
     * Find a project
     *
     * @throws ProjectNotFoundException
     * @return Project
     */
    public function find($id);

    /**
     * Adds a project to the repository
     *
     * @param Project $project
     * @return void
     */
    public function add(Project $project);
}
