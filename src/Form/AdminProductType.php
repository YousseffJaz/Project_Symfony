<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\AdminStockPriceType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

class AdminProductType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
    ->add('title', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]
    ])
    ->add('purchasePrice', NumberType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('alert', NumberType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('category', EntityType::class, array(
      'class' => Category::class,
      'query_builder' => function (EntityRepository $er) {
        return $er->createQueryBuilder('i')
        ->orderBy('i.name', 'ASC');
      },
      'choice_label' => 'name',
      'label' => "Catégorie",
      'placeholder'   =>'Sélectionnez une catégorie',
      'required' => false,
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
    ))
    ->add('stockLists', CollectionType::class, [
      'entry_type' => AdminStockPriceType::class,
      'allow_add' => true,
      'allow_delete' => true
    ])
    ->add('digital', ChoiceType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'choices'  => [
        'Non' => 0,
        'Oui' => 1,
      ]
    ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class' => Product::class,
    ]);
  }
}
