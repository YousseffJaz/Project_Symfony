<?php

namespace App\Form;

use App\Entity\Info;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminInfoType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
    ->add('snap', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]
    ])
    ->add('facebook', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]
    ])
    ->add('whatsapp', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]
    ])
    ->add('instagram', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]
    ])
    ->add('telegram', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]
    ]);
  } 

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Info::class,
    ]);
  }
}
