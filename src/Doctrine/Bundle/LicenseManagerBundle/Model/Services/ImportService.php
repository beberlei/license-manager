<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Model\Services;

use Doctrine\Bundle\LicenseManagerBundle\Entity\Commit;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Author;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Project;

use Doctrine\ORM\EntityManager;

class ImportService
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function import(Project $project)
    {
        if ($project->confirmed()) {
            throw new \RuntimeException("Cannot import already confirmed project");
        }

        $authors = array();

        foreach ($project->getRepositories() as $repository) {
            $url = $repository->getUrl();

            if (strpos($url, "https://github.com/") === false) {
                throw new \InvalidArgumentException("Url should be a github repository.");
            }

            if (substr($url, -4) !== ".git") {
                throw new \InvalidArgumentException("Has to be .git repository");
            }

            $name = substr(str_replace("https://github.com/", "", $url), 0, -4);

            $project->markConfirmed();

            $this->entityManager->persist($project);
            $dirName = str_replace("/", "-", $name);

            chdir("/tmp");
            shell_exec("git clone " . $url . " " . $dirName);
            chdir("/tmp/" . $dirName);

            $output = shell_exec('git log --shortstat --format="%H;%an;%ae;%at;%s"');
            $lines = explode("\n", $output);

            $sha1 = $name = $email = $subject = $changeLine = $time = null;
            foreach ($lines as $line) {

                if (substr_count($line, ";") >= 3) {
                    if ($sha1) {
                        if (!isset($authors[$email])) {
                            $authors[$email] = new Author($name, $email, $project);
                            $this->entityManager->persist($authors[$email]);
                        }

                        if ($changeLine) {
                            $commit = new Commit($sha1, $project, $repository, $authors[$email], $changeLine, new \DateTime('@' . $time));
                            $this->entityManager->persist($commit);
                            $changeLine = null;
                        }
                    }

                    list ($sha1, $name, $email, $time, $subject) = explode(";", $line, 5);

                } else if (trim($line) == "") {
                    continue;
                } else {
                    $changeLine = $line;
                }
            }
        }

        $this->entityManager->flush();
    }
}

