<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Services;

use Doctrine\Bundle\LicenseManagerBundle\Entity\Commit;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Author;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Project;

use Doctrine\ORM\EntityManager;

class ImportService
{
    private $entityManager;
    private $emails;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addEmails(array $emails)
    {
        $this->entityManagerails = $emails;
    }

    public function import($url)
    {
        if (strpos($url, "https://github.com/") === false) {
            throw new \InvalidArgumentException("Url should be a github repository.");
        }

        if (substr($url, -4) !== ".git") {
            throw new \InvalidArgumentException("Has to be .git repository");
        }

        $name = substr(str_replace("https://github.com/", "", $url), 0, -4);

        $projectRepository = $this->entityManager->getRepository('Doctrine\Bundle\LicenseManagerBundle\Entity\Project');
        $project = $projectRepository->findOneBy(array('githubUrl' => $url));

        if ($project === null) {
            $project = new Project($name, $url);
        }

        $project->markConfirmed();

        $this->entityManager->persist($project);
        $dirName = str_replace("/", "-", $name);

        chdir("/tmp");
        shell_exec("git clone " . $url . " " . $dirName);
        chdir("/tmp/" . $dirName);

        $output = shell_exec('git log --shortstat --format="%H;%an;%ae;%at;%s"');
        $lines = explode("\n", $output);

        $authors = array();
        $dql = "SELECT a FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Author a";
        foreach ($this->entityManager->createQuery($dql)->getResult() as $author) {
            $authors[$author->getEmail()] = $author;
        }

        $sha1 = $name = $email = $subject = $changeLine = $time = null;
        foreach ($lines as $line) {

            if (substr_count($line, ";") >= 3) {
                if ($sha1) {
                    if (!isset($authors[$email])) {
                        $authors[$email] = new Author($name, $email);
                        $this->entityManager->persist($authors[$email]);
                    }

                    if ( $changeLine) {
                        $commit = new Commit($sha1, $project, $authors[$email], $changeLine, new \DateTime('@' . $time));
                        $this->entityManager->persist($commit);
                        $changeLine = null;
                    }
                }

                list ($sha1, $name, $email, $time, $subject) = explode(";", $line, 5);

                // example: @625475ce-881a-0410-a577-b389adb331d8
                if (preg_match('(@[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12})', $email)) {
                    if (isset($this->entityManagerails[$name]) && $this->entityManagerails[$name] != "NULL") {
                        $email = $this->entityManagerails[$name];
                    }
                }

            } else if (trim($line) == "") {
                continue;
            } else {
                $changeLine = $line;
            }
        }

        $this->entityManager->flush();
    }
}

