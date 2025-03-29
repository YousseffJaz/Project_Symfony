<?php

declare(strict_types=1);

namespace App\Twig;

use App\Enum\PaymentType;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PaymentTypeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('payment_type_label', [$this, 'getPaymentTypeLabel']),
        ];
    }

    public function getPaymentTypeLabel($value): string
    {
        if (null === $value) {
            return '';
        }

        try {
            return PaymentType::from((int) $value)->getLabel();
        } catch (\ValueError $e) {
            return 'Type invalide';
        }
    }
}
