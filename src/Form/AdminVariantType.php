<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Variant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'autocomplete' => 'off',
            ]]
        )
        ->add('price', NumberType::class, [
            'attr' => [
                'class' => 'form-control',
                'min' => 0.01,
                'max' => 999999.99,
                'step' => 0.01,
            ],
            'label' => 'Prix',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Variant::class,
        ]);
    }
}
