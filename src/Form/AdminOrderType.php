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
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Enum\PaymentMethod;

class AdminOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $statusOptions = [];
        foreach (OrderStatus::cases() as $status) {
            $statusOptions[$status->getLabel()] = $status->value;
        }

        $paymentTypeOptions = [];
        foreach (PaymentType::cases() as $type) {
            $paymentTypeOptions[$type->getLabel()] = $type->value;
        }

        $paymentMethodOptions = [];
        foreach (PaymentMethod::cases() as $method) {
            $paymentMethodOptions[$method->getLabel()] = $method->value;
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
            ->add('paymentType', ChoiceType::class, [
                'placeholder' => 'Choisir un type de paiement',
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off'
                ],
                'choices' => $paymentTypeOptions,
                'required' => false
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'placeholder' => 'Choisir une méthode de paiement',
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off'
                ],
                'choices' => $paymentMethodOptions,
                'required' => false
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
