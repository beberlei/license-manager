<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Commit Controller
 */
class CommitController extends Controller
{
    /**
     * @Extra\Route("/licenses/commit/{id}/trivial", name="licenses_commit_trivial")
     * @Extra\Method("POST")
     * @Extra\Template
     */
    public function trivialAction($id)
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $commit        = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Commit', $id);
        $commit->markTrivial();

        $entityManager->flush();

        return $this->redirect($this->generateUrl('licenses_author_view', array('id' => $commit->getAuthor()->getId())));
    }
}
