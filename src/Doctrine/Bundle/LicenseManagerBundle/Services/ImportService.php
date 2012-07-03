<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Services;

use Doctrine\Bundle\LicenseManagerBundle\Entity\Commit;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Author;
use Doctrine\Bundle\LicenseManagerBundle\Entity\Project;

class ImportService
{
    private $em;
    private $emails;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function addEmails(array $emails)
    {
        $this->emails = $emails;
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

        $project = new Project($name, $url);
        $this->em->persist($project);
        $dirName = str_replace("/", "-", $name);

        chdir("/tmp");
        shell_exec("git clone " . $url . " " . $dirName);
        chdir("/tmp/" . $dirName);

        $output = shell_exec('git log --shortstat --format="%H;%an;%ae;%at;%s"');
        $lines = explode("\n", $output);

        $authors = array();
        $dql = "SELECT a FROM Doctrine\Bundle\LicenseManagerBundle\Entity\Author a";
        foreach ($this->em->createQuery($dql)->getResult() as $author) {
            $authors[$author->getEmail()] = $author;
        }

        $sha1 = $name = $email = $subject = $changeLine = $time = null;
        foreach ($lines as $line) {

            if (substr_count($line, ";") >= 3) {
                if ($sha1) {
                    if (!isset($authors[$email])) {
                        $authors[$email] = new Author($name, $email);
                        $this->em->persist($authors[$email]);
                    }

                    if ( $changeLine) {
                        $commit = new Commit($sha1, $project, $authors[$email], $changeLine, new \DateTime('@' . $time));
                        $this->em->persist($commit);
                        $changeLine = null;
                    }
                }

                list ($sha1, $name, $email, $time, $subject) = explode(";", $line, 5);

                // example: @625475ce-881a-0410-a577-b389adb331d8
                if (preg_match('(@[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12})', $email)) {
                    if (isset($this->emails[$name]) && $this->emails[$name] != "NULL") {
                        $email = $this->emails[$name];
                    }
                }

            } else if (trim($line) == "") {
                continue;
            } else {
                $changeLine = $line;
            }
        }

        $this->em->flush();
    }
}

