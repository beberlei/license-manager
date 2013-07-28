<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $dql = "SELECT p AS project,
                    (SELECT SUM(c.insertions) FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Commit c WHERE c.project = p.id) as insertions,
                    (SELECT SUM(c2.insertions) FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Commit c2 INNER JOIN c2.author a WHERE a.approved = 1 AND c2.project = p.id) as confirmed_insertions,
                    (SELECT SUM(c3.insertions) FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Commit c3 INNER JOIN c3.author a2 WHERE a2.approved != 1 AND c3.project = p.id AND c3.trivial = true) as trivial_insertions
                 FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Project p
                WHERE p.confirmed = true
             ORDER BY p.name ASC";
        $projects = $em->createQuery($dql)->getResult();

        for ($i = 0; $i < count($projects); $i++) {
            $projects[$i]['ratio'] = number_format(
                $projects[$i]['confirmed_insertions'] / $projects[$i]['insertions'] * 100, 2, ",", ""
            );
            $projects[$i]['ratio_trivial'] = number_format(
                $projects[$i]['trivial_insertions'] /  $projects[$i]['insertions'] * 100, 2, ",", ""
            );
        }

        return array('projects' => $projects);
    }

    /**
     * @Extra\Route("/licenses/projects/send", name="licenses_project_request")
     * @Extra\Method("GET")
     * @Extra\Template
     */
    public function requestAction()
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $qb = $em->createQueryBuilder();
        $qb->from('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', 'a')
           ->select('count(DISTINCT a.id)')
           ->innerJoin('a.commits', 'c')
           ->where('a.approved = 0');

        $authors = $qb->getQuery()->getSingleScalarResult();
        if (!$authors) {
            return $this->redirect($this->generateUrl('licenses_projects'));
        }

        return array('authors' => $authors);
    }

    /**
     * @Extra\Route("/licenses/projects/send", name="licenses_project_send")
     * @Extra\Method("POST")
     */
    public function sendAction()
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $qb = $em->createQueryBuilder();
        $qb->from('Doctrine\Bundle\LicenseManagerBundle\Entity\Author', 'a')
           ->select('DISTINCT a')
           ->innerJoin('a.commits', 'c')
           ->where('a.approved = 0');
        $authors = $qb->getQuery()->getResult();

        $client = new \Buzz\Browser(new \Buzz\Client\Curl);
        $headers = array('Authorization: Basic ' . base64_encode($this->container->getParameter('mailgun_apikey')));
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
            $link = $this->generateUrl('author_approve', array('id' => $author->getId(), 'hash' => $expected), true);

            $content = http_build_query(array(
                'from'    => 'Benjamin Eberlei <kontakt@beberlei.de>',
                'to'      => $author->getEmail(),
                'subject' => 'Your answer needed: Doctrine PHP Project License Change',
                'text'    => $this->renderView('DoctrineLicenseManagerBundle:Project:email.txt.twig', array(
                    'author' => $author,
                    'link'   => $link
                ))
            ));
            $client->post('https://api.mailgun.net/v2/doctrine.mailgun.org/messages', $headers, $content);
        }

        return $this->redirect($this->generateUrl('licenses_projects'));
    }
}

