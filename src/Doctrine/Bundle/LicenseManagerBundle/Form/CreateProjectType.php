<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Project Name'))
            ->add('githubUrl', 'url', array('label' => 'Github Repository URL'))
            ->add('pageMessage', 'textarea', array('label' => 'Message on Approval Page'))
            ->add('emailMessage', 'textarea', array('label' => 'Message in Mail to Contributor'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Doctrine\Bundle\LicenseManagerBundle\Model\Commands\CreateProject'
        ));
    }

    public function getName()
    {
        return 'create_project';
    }
}

