<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Reseller;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminUserType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
    ->add('reseller', EntityType::class, [
      'class' => Reseller::class,
      'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('r')
      ->orderBy('r.name', 'ASC'),
      'choice_label' => 'name',
      'label' => "Associer à un revendeur",
      'placeholder'   =>'Sélectionner un revendeur',
      "required" => false
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
