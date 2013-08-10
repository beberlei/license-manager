<?php

namespace Doctrine\Bundle\LicenseManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $licenseOptions = array();
        foreach ($options['licenses'] as $license) {
            $licenseOptions[$license->getId()] = $license->getName();
        }

        $builder
            ->add('name', 'text', array('label' => 'Project Name'))
            ->add('url', 'collection', array('type' => 'text', 'allow_add' => true, 'label' => 'Github Repository URL'))
            ->add('pageMessage', 'textarea', array('label' => 'Message on Approval Page', 'attr' => array('class' => 'span5', 'rows' => 6)))
            ->add('emailMessage', 'textarea', array('label' => 'Message in Mail to Contributor', 'attr' => array('class' => 'span5', 'rows' => 6)))
            ->add('senderName', 'text', array('label' => 'Sender Name'))
            ->add('senderMail', 'email', array('label' => 'Sender e-Mail'))
            ->add('fromLicense', 'choice', array('choices' => $licenseOptions, 'label' => 'From License'))
            ->add('toLicense', 'choice', array('choices' => $licenseOptions, 'label' => 'To License'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('licenses'));
        $resolver->setDefaults(array(
            'data_class' => 'Doctrine\Bundle\LicenseManagerBundle\Model\Commands\CreateProject'
        ));
    }

    public function getName()
    {
        return 'create_project';
    }
}

