<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Enum\PaymentMethod;
use App\Enum\PaymentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => function (Customer $customer) {
                    return $customer->getFirstname().' '.$customer->getLastname().' ('.$customer->getEmail().')';
                },
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'required' => true,
                'placeholder' => 'Choisir un client',
            ])
            ->add('total', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('shippingCost', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('discount', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('paid', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('note', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('orderStatus', ChoiceType::class, [
                'placeholder' => 'Choisir un statut',
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'choices' => $statusOptions,
            ])
            ->add('paymentType', ChoiceType::class, [
                'placeholder' => 'Choisir un type de paiement',
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'choices' => $paymentTypeOptions,
                'required' => false,
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'placeholder' => 'Choisir une méthode de paiement',
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'choices' => $paymentMethodOptions,
                'required' => false,
            ])
            ->add('trackingId', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
