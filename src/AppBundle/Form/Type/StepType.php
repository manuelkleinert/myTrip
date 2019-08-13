<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('test', TextType::class, [
                'label' => false,
                'required' => true,
            ])
            ->add('dateTime', TextType::class)
            ->add('dateTimeTo', TextType::class)
            ->add('geoLat', HiddenType::class)
            ->add('geoLong', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'app.configurator.next',
                'attr' => ['class' => 'uk-button uk-button-primary']
            ]);
    }
}