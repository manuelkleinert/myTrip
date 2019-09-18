<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class StepEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('title', TextType::class, [
                'label' => 'app.form.step.label.title',
                'attr' => ['placeholder' =>'app.form.step.label.placeholder.title']
                ])
            ->add('dateFrom', TextType::class, [
                'label' => 'app.form.step.label.date.from',
                'attr' => ['placeholder' =>'app.form.step.placeholder.date.from']
            ])
            ->add('timeFrom', TextType::class, [
                'label' => 'app.form.step.label.time.from',
                'attr' => ['placeholder' =>'app.form.step.placeholder.time.from']
            ])
            ->add('dateTo', TextType::class, [
                'label' => 'app.form.step.label.date.to',
                'attr' => ['placeholder' =>'app.form.step.placeholder.date.to']
            ])
            ->add('timeTo', TextType::class, [
                'label' => 'app.form.step.label.time.to',
                'attr' => ['placeholder' =>'app.form.step.placeholder.time.to']
            ])
            ->add('lat', TextType::class, [
                'label' => 'app.form.step.label.time.to',
                'disabled' => true,
                'attr' => ['placeholder' =>'app.form.step.placeholder.lat']
            ])
            ->add('lng', TextType::class, [
                'label' => 'app.form.step.label.time.to',
                'disabled' => true,
                'attr' => ['placeholder' =>'app.form.step.placeholder.lng']
            ])
            ->add('update', ButtonType::class, [
                'label' => 'app.form.step.button.add',
                'attr' => ['class' => 'uk-button uk-button-primary']
            ]);
    }
}
