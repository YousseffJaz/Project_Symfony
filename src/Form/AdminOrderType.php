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
use Doctrine\ORM\EntityRepository;

class AdminOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => function ($customer) {
                    return $customer->getFirstname().' '.$customer->getLastname().' ('.$customer->getEmail().')';
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.lastname', 'ASC');
                },
                'required' => true,
            ])
            ->add('shippingCost', NumberType::class, [
                'required' => false,
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'step' => 0.01,
                ],
            ])
            ->add('total', NumberType::class, [
                'required' => false,
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'step' => 0.01,
                ],
            ])
            ->add('discount', NumberType::class, [
                'required' => false,
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'step' => 0.01,
                ],
            ])
            ->add('paid', NumberType::class, [
                'required' => false,
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'step' => 0.01,
                ],
            ])
            ->add('paymentType', ChoiceType::class, [
                'choices' => [
                    'En ligne' => PaymentType::ONLINE->value,
                    'Sur place' => PaymentType::LOCAL->value,
                ],
                'required' => true,
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'choices' => [
                    'Espèces' => PaymentMethod::CASH->value,
                    'Carte bancaire' => PaymentMethod::CARD->value,
                    'Chèque' => PaymentMethod::CHECK->value,
                    'Virement bancaire' => PaymentMethod::BANK->value,
                ],
                'required' => true,
            ])
            ->add('orderStatus', ChoiceType::class, [
                'choices' => [
                    'En attente' => OrderStatus::WAITING->value,
                    'Payé' => OrderStatus::PAID->value,
                    'Partiellement payé' => OrderStatus::PARTIAL->value,
                    'Remboursé' => OrderStatus::REFUND->value,
                ],
                'required' => true,
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
            ])
        ;

        if ($options['is_edit']) {
            $builder->add('trackingId', TextType::class, [
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'is_edit' => true,
        ]);
    }
}
