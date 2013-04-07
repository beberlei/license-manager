<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
      * @Extra\Route("/licenses/authors/update/{id}", name="licenses_authors_update")
      * @Extra\Method("POST")
      */
    public function updateAction($id)
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $email = $this->getRequest()->request->get('email');

        $author = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);
        $author->setEmail($email);

        $entityManager->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('{"ok":true}', 200, array('Content-Type' => 'application/json'));
        } else {
            return $this->redirect($this->generateUrl('licenses_authors'));
        }
    }

    /**
     * @Extra\Route("/licenses/author/{id}", name="licenses_author_view")
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

        $expected = sha1($this->container->getParameter('secret') . $id . $author->getEmail());

        return array('author' => $author, 'revisions' => $revisions, 'commits' => $commits, 'expectedHash' => $expected);
    }

    /**
     * @Extra\Route("/licenses/author/approve/{id}", name="licenses_author_approve")
     * @Extra\Method("POST")
     */
    public function approveAction($id)
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $author = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);

        $author->approve();
        $entityManager->flush();

        $this->container->get('session')->setFlash('notice', 'Author ' . $author->getEmail() . ' was approved.');

        return $this->redirect($this->generateUrl('licenses_authors'));
    }

    /**
     * @Extra\Route("/licenses/approve/{id}", name="author_approve")
     * @Extra\Template
     */
    public function authorApproveAction($id)
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $author = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', $id);
        if (!$author) {
            throw $this->createNotFoundException();
        }

        $hash = $this->getRequest()->query->get('hash', '');
        $expected = sha1($this->container->getParameter('secret') . $id . $author->getEmail());
        if ($expected != $hash) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new \Doctrine\Bundle\LicenseManagerBundle\Form\ApproveType(), $author);

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                $entityManager->flush();
                $this->container->get('session')->setFlash('notice', 'Thank you for your response to the license change.');

                return $this->redirect($this->generateUrl('author_approve', array('id' => $id, 'hash' => $hash)));
            }
        }

        return array('form' => $form->createView(), 'author' => $author, 'expectedHash' => $expected);
    }
}

