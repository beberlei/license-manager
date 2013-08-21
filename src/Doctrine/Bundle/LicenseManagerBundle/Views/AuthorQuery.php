<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Views;

interface AuthorQuery
{
    /**
     * @param int $projectId
     * @return int
     */
    public function authorCount($projectId);

    /**
     * @return AuthorApprovalReport
     */
    public function queryAuthorApproval($projectId, $onlyUnapproved = false);
}
