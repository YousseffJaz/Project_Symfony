<?php

namespace App\Form;

use App\Entity\Order;
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
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $statusOptions = [
            'En attente' => 0,
            'En cours' => 1,
            'Terminé' => 2,
            'Annulé' => 3,
            'En livraison' => 4,
            'Livré' => 5,
        ];

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
            ]);

        /** @var Admin */
        $admin = $this->tokenStorage->getToken()->getUser();

        if ($admin->getRole() === "ROLE_SUPER_ADMIN") {
            $builder->add('delivery', EntityType::class, [
                'class' => Admin::class,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('a')
                    ->where('a.role = :role')
                    ->setParameter('role', 'ROLE_LIVREUR')
                    ->orderBy('a.firstname', 'ASC'),
                'choice_label' => function ($admin) {
                    return $admin->getFirstname() . ' ' . $admin->getLastname();
                },
                'placeholder' => 'Sélectionnez un livreur',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
