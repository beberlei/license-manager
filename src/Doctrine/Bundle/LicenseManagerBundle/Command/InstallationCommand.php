<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InstallationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('license-manager:install')
            ->setDescription('Install Fixtures and such')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installer = $this->getContainer()->get('doctrine_license_manager.installer');
        $installer->install();
    }
}
