<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
     * @Extra\Route("/licenses/projects/{id}/approve", name="licenses_project_approve")
     * @Extra\Method("POST")
     * @Extra\Template
     */
    public function approveAction($id)
    {
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
        $form = $this->createForm(new CreateProjectType());

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $createProject = $form->getData();
                $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

                $project = new Project($createProject->name, $createProject->githubUrl);
                $project->setPageMessage($createProject->pageMessage);
                $project->setEmailMessage($createProject->emailMessage);

                $entityManager->persist($project);
                $entityManager->flush();

                $request->getSession()->getFlashBag()->set('success', 'You created a new license switch project. We will evaluate your request and respond timely.');

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
    public function sendAction($id)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $project = $em->find('Doctrine\Bundle\LicenseManagerBundle\Entity\Project', $id);

        if (!$project) {
            throw $this->createNotFoundException();
        }

        $qb = $em->createQueryBuilder();
        $qb->from('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', 'a')
           ->select('DISTINCT a')
           ->innerJoin('a.commits', 'c')
           ->where('a.approved = 0')
           ->andWhere('a.project = ?1');

        $authors = $qb->getQuery()->setParameter(1, $id)->getResult();

        $client = new \Buzz\Browser(new \Buzz\Client\Curl);
        $headers = array('Authorization: Basic ' . base64_encode($this->container->getParameter('mailgun_apikey')));
        $mailgunDomain = $this->container->getParameter('mailgun_domain');
        $emails = array();

        foreach ($authors as $author) {
            if (isset($emails[$author->getEmail()])) {
                continue;
            }

            $emails[$author->getEmail()] = true;

            $parts = explode("@", $author->getEmail());
            if (strpos($parts[1], ".") === false) {
                continue;
            }

            $expected = sha1($this->container->getParameter('secret') . $author->getId() . $author->getEmail());
            $link = $this->generateUrl('author_approve', array(
                'id' => $author->getId(),
                'hash' => $expected,
            ), true);

            $content = http_build_query(array(
                'from'    => 'Benjamin Eberlei <kontakt@beberlei.de>',
                'to'      => $author->getEmail(),
                'subject' => sprintf('Your answer needed: %s License Change', $project->getName()),
                'text'    => $this->renderView('DoctrineLicenseManagerBundle:Project:email.txt.twig', array(
                    'project' => $project,
                    'author' => $author,
                    'link'   => $link
                ))
            ));

            $client->post('https://api.mailgun.net/v2/' . $mailgunDomain . '/messages', $headers, $content);
        }

        return $this->redirect($this->generateUrl('licenses_projects'));
    }
}

