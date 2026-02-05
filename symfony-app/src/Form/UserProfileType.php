<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\Form\DataTransformer\MaskedTokenTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phoenixApiToken', TextType::class, [
                'label' => 'Phoenix API Token',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter your Phoenix API access token',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Token',
            ]);

        $builder->get('phoenixApiToken')
            ->addModelTransformer(new MaskedTokenTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
