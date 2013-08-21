<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Views;

interface ProjectQuery
{
    /**
     * Show all projects and their current state with regard to success.
     *
     * @param bool $isAdmin
     * @return ProjectReport[]
     */
    public function queryAll($isAdmin = false);
}
