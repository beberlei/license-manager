<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ApproveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('approved', 'choice', array(
                'choices' => array(
                    '0' => '-- Select an option --',
                    '1' => 'I approve',
                    '2' => 'I don\' approve',
                    '3' => 'You contacted the wrong person.',
                )
            ))
        ;
    }

    public function getName()
    {
        return 'licenses_approve';
    }

    public function getDefaultOptions(array $options)
    {
        return array(

        );
    }
}

