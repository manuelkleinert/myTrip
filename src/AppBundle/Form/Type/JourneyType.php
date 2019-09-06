<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class JourneyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('title', TextType::class, [
                'label' => false,
                'required' => true,
            ])
            ->add('subTitle', TextType::class, [
                'label' => false,
                'required' => true,
            ])
            ->add('from', DateType::class)
            ->add('to', DateType::class)
            ->add('private', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'app.journey.create.button',
                'attr' => ['class' => 'uk-button uk-button-primary']
            ]);
    }
}
