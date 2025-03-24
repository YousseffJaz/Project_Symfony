<?php

namespace App\Form;

use App\Entity\Variant;
use App\Form\AdminPriceListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdminVariantType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
    ->add('title', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]]
    )
    ->add('priceLists', CollectionType::class, [
      'entry_type' => AdminPriceListType::class,
      'allow_add' => true,
      'allow_delete' => true
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Variant::class,
    ]);
  }
}
