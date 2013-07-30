<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Author Controller
 */
class AuthorController extends Controller
{
    /**
     * @Extra\Route("/licenses/authors", name="licenses_authors")
     * @Extra\Method("GET")
     * @Extra\Template
     */
    public function manageAction()
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

        $qb = $entityManager->createQueryBuilder();
        $qb->from('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', 'a')
           ->select('a AS author, SUM(c.insertions) AS insertions, SUM(c.deletions) AS deletions')
           ->innerJoin('a.commits', 'c')
           ->groupBy('a.id')
           ->orderBy('insertions', 'DESC');

        if ($this->getRequest()->query->has('project')) {
            $qb->andWhere('c.project = ?1')
               ->setParameter(1, $this->getRequest()->query->get('project'));
        }

        if ($this->getRequest()->query->has('unapproved')) {
            $qb->andWhere('a.approved = false');
        }

        $authors = $qb->getQuery()->getResult();

        $approvedCount = 0;
        foreach ($authors as $data) {
            if ($data['author']->getApproved() == 1) {
                $approvedCount++;
            }
        }
        $missing = count($authors) - $approvedCount;

        return array(
            'authors' => $authors,
            'missing' => $missing,
            'approveRatio' => count($authors) ? number_format($approvedCount / count($authors) * 100, 2) : 0,
        );
    }

     /**
      * @Extra\Route("/licenses/authors/{id}/update", name="licenses_authors_update")
      * @Extra\Method("POST")
      */
    public function updateAction($id)
    {
        $this->assertIsRole('ROLE_ADMIN');

        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $email = $this->getRequest()->request->get('email');

        $author = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);
        $author->setEmail($email);

        $entityManager->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('{"ok":true}', 200, array('Content-Type' => 'application/json'));
        }

        return $this->redirect($this->generateUrl('licenses_authors'));
    }

    /**
     * @Extra\Route("/licenses/authors/{id}", name="licenses_author_view")
     * @Extra\Method("GET")
     * @Extra\Template
     */
    public function viewAction($id)
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $author = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);

        $revisions = $this->get('simplethings_entityaudit.reader')->findRevisions('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);

        $dql = "SELECT c FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Commit c WHERE c.author = ?1 ORDER BY c.created ASC";
        $query = $entityManager->createQuery($dql);
        $query->setParameter(1, $author->getId());

        $commits = new Pagerfanta(new DoctrineORMAdapter($query));
        $commits->setMaxPerPage(50);
        $commits->setCurrentPage($this->getRequest()->get('page', 1));

        $expected = hash_hmac('sha512', $id . $author->getEmail(), $this->container->getParameter('secret'));

        return array('author' => $author, 'revisions' => $revisions, 'commits' => $commits, 'expectedHash' => $expected);
    }

    /**
     * @Extra\Route("/licenses/authors/{id}/admin-approve", name="licenses_author_approve")
     * @Extra\Method("POST")
     */
    public function approveAction($id, Request $request)
    {
        $this->assertIsRole('ROLE_ADMIN');

        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $author = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);

        $author->approve();
        $entityManager->flush();

        $this->container->get('session')->setFlash('notice', 'Author ' . $author->getEmail() . ' was approved.');

        return $this->redirect($this->generateUrl('licenses_authors'));
    }

    /**
     * @Extra\Route("/licenses/authors/{id}/approve", name="author_approve")
     * @Extra\Method({"GET", "POST"})
     * @Extra\Template
     */
    public function authorApproveAction($id)
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $author = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);

        if (!$author) {
            throw $this->createNotFoundException();
        }

        $project = $author->getProject();

        $hash = $this->getRequest()->query->get('hash', '');
        $expected = hash_hmac('sha512', $id . $author->getEmail(), $this->container->getParameter('secret'));

        if ($expected != $hash) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new \Doctrine\Bundle\LicenseManagerBundle\Form\ApproveType(), $author);

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                $entityManager->flush();
                $this->container->get('session')->setFlash('notice', 'Thank you for your response to the license change.');

                return $this->redirect($this->generateUrl('author_approve', array('id' => $id, 'hash' => $hash)));
            }
        }

        return array('form' => $form->createView(), 'author' => $author, 'expectedHash' => $expected, 'project' => $project);
    }

    private function assertIsRole($role)
    {
        if (!$this->container->get('security.context')->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }
    }
}

