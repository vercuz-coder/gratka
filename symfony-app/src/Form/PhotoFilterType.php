<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\PhotoFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhotoFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', TextType::class, [
                'required' => false,
                'label' => 'Location',
                'attr' => ['placeholder' => 'e.g. Warsaw'],
            ])
            ->add('camera', TextType::class, [
                'required' => false,
                'label' => 'Camera',
                'attr' => ['placeholder' => 'e.g. Canon'],
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'label' => 'Description',
                'attr' => ['placeholder' => 'Search in description...'],
            ])
            ->add('username', TextType::class, [
                'required' => false,
                'label' => 'Username',
                'attr' => ['placeholder' => 'e.g. john_doe'],
            ])
            ->add('takenAtFrom', DateType::class, [
                'required' => false,
                'label' => 'Date from',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('takenAtTo', DateType::class, [
                'required' => false,
                'label' => 'Date to',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PhotoFilter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
