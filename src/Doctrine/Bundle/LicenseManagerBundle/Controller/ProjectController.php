<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Bundle\LicenseManagerBundle\Model\Commands\CreateProject;
use Doctrine\Bundle\LicenseManagerBundle\Form\CreateProjectType;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Project;

/**
 * Author Controller
 */
class ProjectController extends Controller
{
    /**
     * @Extra\Route("/licenses/projects", name="licenses_projects")
     * @Extra\Method("GET")
     * @Extra\Template
     */
    public function indexAction()
    {
        $where = $this->container->get('security.context')->isGranted('ROLE_ADMIN')
            ? '1=1'
            : 'p.confirmed = true';

        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $dql = "SELECT p AS project,
                    (SELECT SUM(c.insertions) FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Commit c WHERE c.project = p.id) as insertions,
                    (SELECT SUM(c2.insertions) FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Commit c2 INNER JOIN c2.author a WHERE a.approved = 1 AND c2.project = p.id) as confirmed_insertions,
                    (SELECT SUM(c3.insertions) FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Commit c3 INNER JOIN c3.author a2 WHERE a2.approved != 1 AND c3.project = p.id AND c3.trivial = true) as trivial_insertions
                 FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Project p
                WHERE $where
             ORDER BY p.name ASC";
        $projects = $em->createQuery($dql)->getResult();

        for ($i = 0; $i < count($projects); $i++) {
            $projects[$i]['ratio'] = "-/-";
            $projects[$i]['ratio_trivial'] = "-/-";

            if ($projects[$i]['project']->confirmed()) {
                $projects[$i]['ratio'] = number_format(
                    $projects[$i]['confirmed_insertions'] / $projects[$i]['insertions'] * 100, 2, ",", ""
                );
                $projects[$i]['ratio_trivial'] = number_format(
                    $projects[$i]['trivial_insertions'] /  $projects[$i]['insertions'] * 100, 2, ",", ""
                );
            }
        }

        return array('projects' => $projects);
    }

    /**
     * @Extra\Route("licenses/projects/{id}", name="licenses_project_view", requirements={"id": "\d+"})
     * @Extra\Template
     */
    public function viewAction($id)
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $project = $entityManager->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Project', $id);

        $qb = $entityManager->createQueryBuilder();
        $qb->from('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', 'a')
           ->select('a AS author, SUM(c.insertions) AS insertions, SUM(c.deletions) AS deletions')
           ->innerJoin('a.commits', 'c')
           ->where('c.project = ?1')->setParameter(1, $id)
           ->groupBy('a.id')
           ->orderBy('insertions', 'DESC');

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
            'project' => $project,
            'authors' => $authors,
            'missing' => $missing,
            'approveRatio' => count($authors) ? number_format($approvedCount / count($authors) * 100, 2) : 0,
        );
    }

    /**
     * @Extra\Route("/licenses/projects/{id}/approve", name="licenses_project_approve")
     * @Extra\Method("POST")
     * @Extra\Template
     */
    public function approveAction($id)
    {
        $this->assertIsRole('ROLE_ADMIN');

        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $project = $em->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Project', $id);

        if (!$project) {
            throw $this->createNotFoundException();
        }

        $importer = $this->container->get('doctrine_license_manager.importer');
        $importer->import($project);

        return $this->redirect($this->generateUrl('licenses_projects'));
    }

    /**
     * @Extra\Route("/licenses/projects/create", name="licenses_project_create")
     * @Extra\Method({"GET", "POST"})
     * @Extra\Template
     */
    public function createAction(Request $request)
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $licenseRepository = $entityManager->getRepository('Doctrine\Bundle\LicenseManagerBundle\Entity\License');
        $licenses  = $licenseRepository->findBy(array(), array('name' => 'ASC'));

        $command = new CreateProject();
        $form = $this->createForm(new CreateProjectType(), $command, array('licenses' => $licenses));

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $createProject = $form->getData();

                $project = new Project($createProject->name);
                foreach ($createProject->url as $url) {
                    $project->addRepository($url);
                }
                $project->setPageMessage($createProject->pageMessage);
                $project->setEmailMessage($createProject->emailMessage);
                $project->setSender($createProject->senderName, $createProject->senderMail);
                $project->setFromLicense($licenseRepository->find($createProject->fromLicense));
                $project->setToLicense($licenseRepository->find($createProject->toLicense));

                $entityManager->persist($project);
                $entityManager->flush();

                $request->getSession()->getFlashBag()->set('success', 'You created a new license switch project. We will evaluate your request and respond timely.');
                $mailer = $this->container->get('doctrine_license_manager.mailer');
                $mailer->sendTextMessage(
                    $this->container->getParameter('mailer_sender'),
                    $this->container->getParameter('mailer_admin_email'),
                    'New License Switch Project registered',
                    sprintf(
                        "Hello!\n\nA new project was registered on License Switcher:\n\nName: %s\nURL: %s\nFrom %s To %s\nPage Message:\n\n%s\n\nE-Mail Message:\n\n%s",
                        $createProject->name,
                        implode(", ", $createProject->url),
                        $project->getFromLicense()->getName(),
                        $project->getToLicense()->getName(),
                        $createProject->pageMessage,
                        $createProject->emailMessage
                    )
                );

                return $this->redirect($this->generateUrl('licenses_projects'));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Extra\Route("/licenses/projects/{id}/send", name="licenses_project_request")
     * @Extra\Method("GET")
     * @Extra\Template
     */
    public function requestAction($id, Request $request)
    {
        $this->assertIsRole('ROLE_ADMIN');

        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $project = $em->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Project', $id);

        if (!$project) {
            throw $this->createNotFoundException();
        }

        $qb = $em->createQueryBuilder();
        $qb->from('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', 'a')
           ->select('count(DISTINCT a.id)')
           ->innerJoin('a.commits', 'c')
           ->where('a.approved = 0')
           ->andWhere('a.project = ?1');

        $authors = $qb->getQuery()->setParameter(1, $id)->getSingleScalarResult();

        if (!$authors) {
            $request->getSession()->getFlashBag()->set('warning', 'Project has no authors that have not approved the license switch.');

            return $this->redirect($this->generateUrl('licenses_projects'));
        }

        return array('authors' => $authors, 'project' => $project);
    }

    /**
     * @Extra\Route("/licenses/projects/{id}/send", name="licenses_project_send")
     * @Extra\Method("POST")
     */
    public function sendAction($id, Request $request)
    {
        $this->assertIsRole('ROLE_ADMIN');

        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $project = $em->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Project', $id);

        if (!$project) {
            throw $this->createNotFoundException();
        }

        $qb = $em->createQueryBuilder();
        $qb->from('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', 'a')
           ->select('a')
           ->where('a.approved = 0')
           ->andWhere('a.project = ?1');

        $authors = $qb->getQuery()->setParameter(1, $id)->getResult();

        $mailer = $this->container->get('doctrine_license_manager.mailer');

        foreach ($authors as $author) {

            $parts = explode("@", $author->getEmail());
            if (strpos($parts[1], ".") === false) {
                continue;
            }

            $expected = hash_hmac('sha512', $author->getId() . $author->getEmail(), $this->container->getParameter('secret'));

            $link = $this->generateUrl('author_approve', array(
                'id' => $author->getId(),
                'hash' => $expected,
            ), true);

            $mailer->sendTextMessage(
                sprintf('%s <%s>', $project->getSenderName(), $project->getSenderMail()),
                $author->getEmail(),
                sprintf('Your answer needed: %s License Change', $project->getName()),
                $this->renderView('DoctrineLicenseManagerBundle:Project:email.txt.twig', array(
                    'project' => $project,
                    'author' => $author,
                    'link'   => $link
                ))
            );
        }

        $request->getSession()->getFlashBag()->set('success', 'Sent request for approval emails to ' . count($emails) . ' contributors.');

        return $this->redirect($this->generateUrl('licenses_projects'));
    }

    private function assertIsRole($role)
    {
        if (!$this->container->get('security.context')->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }
    }
}

