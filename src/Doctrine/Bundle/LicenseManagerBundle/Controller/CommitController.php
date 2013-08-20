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
     * @Extra\Route("/licenses/commits/{id}/trivial", name="licenses_commit_trivial")
     * @Extra\Method("POST")
     * @Extra\Template
     */
    public function trivialAction($id)
    {
        $this->assertIsRole('ROLE_ADMIN');

        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $commit        = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Commit', $id);
        $commit->markTrivial();

        $entityManager->flush();

        return $this->redirect($this->generateUrl('licenses_author_view', array('id' => $commit->getAuthor()->getId())));
    }

    private function assertIsRole($role)
    {
        if (!$this->container->get('security.context')->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }
    }
}
