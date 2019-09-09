<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('from', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('to', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('private', CheckboxType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'app.journey.create.button',
                'attr' => ['class' => 'uk-button uk-button-primary']
            ]);
    }
}
