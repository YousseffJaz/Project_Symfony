<?php

namespace App\Enum;

enum PaymentMethod: int
{
    case CASH = 0;
    case TRANSCASH = 1;
    case CARD = 2;
    case PAYPAL = 3;
    case PCS = 4;
    case CHECK = 5;
    case PAYSAFECARD = 6;
    case BANK = 7;

    public function getLabel(): string
    {
        return match($this) {
            self::CASH => 'Espèces',
            self::TRANSCASH => 'Transcash',
            self::CARD => 'Carte bancaire',
            self::PAYPAL => 'PayPal',
            self::PCS => 'PCS',
            self::CHECK => 'Chèque',
            self::PAYSAFECARD => 'Paysafecard',
            self::BANK => 'Virement bancaire',
        };
    }
} 