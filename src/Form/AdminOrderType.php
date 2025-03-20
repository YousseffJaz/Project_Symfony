<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\Admin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class AdminOrderType extends AbstractType
{
  public function __construct(private TokenStorageInterface $tokenStorage)
  {
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $user = $this->tokenStorage->getToken()->getUser();

    $statusOptions = [
      'Commande à expédier' => 1,
      'Commande à livrer' => 4,
      'Commande terminée' => 3,
    ];

    if ($user instanceof Admin && $user->getRole() === "ROLE_LIVREUR") {
      $statusOptions = [
        'Commande terminée' => 3,
      ];
    }

    $builder
    ->add('firstname', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('lastname', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('phone', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('email', EmailType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('paymentMethod', ChoiceType::class, [
      'placeholder' => 'Choisir un moyen de paiement',
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'choices'  => [
        'Espèce' => 0,
        'Transcash' => 1,
        'Carte bancaire' => 2,
        'Paypal' => 3,
        'PCS' => 4,
        'Chèque' => 5,
        'Paysafecard' => 6,
        'Virement bancaire' => 7
      ]
    ])
    ->add('paymentType', ChoiceType::class, [
      'placeholder' => 'Choisir un type de vente',
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'choices'  => [
        'Internet' => 0,
        'Physique' => 1,
        'Livraison' => 2,
      ]
    ])
    ->add('total', NumberType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]
    ])
    ->add('shippingCost', NumberType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('discount', NumberType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('paid', NumberType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ]
    ])
    ->add('note', TextareaType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('address', TextareaType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('orderStatus', ChoiceType::class, [
      'placeholder' => 'Choisir un statut',
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'choices' => $statusOptions
    ])
    ->add('createdAt', DateTimeType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'date_format' => 'dd/MM/yyyy',
      'required' => false
    ])
    ->add('trackingId', TextType::class, [
      'attr' => [
        'class' => 'form-control',
        'autocomplete' => 'off'
      ],
      'required' => false
    ])
    ->add('note2', EntityType::class, [
      'class' => Note::class,
      'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('i')
      ->orderBy('i.name', 'ASC'),
      'choice_label' => 'name',
      'label' => "Note",
      'placeholder'   =>'Sélectionnez une note',
      'required' => false
    ])
    ->add('delivery', EntityType::class, [
      'class' => Admin::class,
      'choice_label' => fn(Admin $admin) => ucfirst($admin->getFirstName()),
      'label' => 'Livraison',
      'placeholder' => 'Sélectionnez un livreur',
      'required' => false,
      'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
      ->where('a.role = :role')
      ->andWhere('a.archive = false')
      ->setParameter('role', 'ROLE_LIVREUR'),
    ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Order::class,
    ]);
  }
}
