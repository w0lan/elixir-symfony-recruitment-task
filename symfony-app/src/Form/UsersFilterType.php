<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Model\UsersFilterData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UsersFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'required' => false,
                'property_path' => 'firstName',
            ])
            ->add('last_name', TextType::class, [
                'required' => false,
                'property_path' => 'lastName',
            ])
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'placeholder' => '',
                'choices' => [
                    'male' => 'male',
                    'female' => 'female',
                ],
            ])
            ->add('birthdate_from', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'property_path' => 'birthdateFrom',
            ])
            ->add('birthdate_to', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'property_path' => 'birthdateTo',
            ])
            ->add('page_size', ChoiceType::class, [
                'required' => false,
                'property_path' => 'pageSize',
                'choices' => UsersFilterData::AVAILABLE_PAGE_SIZES,
                'placeholder' => false, // Don't show empty option if value is set
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsersFilterData::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
