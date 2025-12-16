<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Model\UserFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'male' => 'male',
                    'female' => 'female',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserFormData::class,
        ]);
    }
}
