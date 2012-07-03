<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProjectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('license:import')
            ->setDescription('Import a project into the license manager.')
            ->addArgument('url', InputArgument::REQUIRED, 'Github Url')
            ->addArgument('emails', InputArgument::OPTIONAL, 'Email Map CSV File with "ImportEmail;RealEmail" format.')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url       = $input->getArgument('url');
        $emailFile = $input->getARgument('emails');
        $emails    = array();

        if ($emailFile) {
            $fh = fopen(getcwd() . "/" . $emailFile, 'r');
            while ($row = fgetcsv($fh, 4096, "\t")) {
                $emails[$row[0]] = $row[1];
            }
        }

        $importer = $this->getContainer()->get('doctrine_license_manager.importer');
        $importer->addEmails($emails);
        $importer->import($url);
    }
}

