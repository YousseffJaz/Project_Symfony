<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\Admin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class AdminTaskType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
    ->add('name', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
    ])
    ->add('admin', EntityType::class, [
      'class' => Admin::class,
      'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
      ->where('a.archive = false')
      ->orderBy('a.firstName', 'ASC'),
      'choice_label' => 'firstName',
      'label' => "Utilisateur",
      'placeholder'   =>'Choisir un utilisateur',
      'required' => false
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Task::class,
    ]);
  }
}
