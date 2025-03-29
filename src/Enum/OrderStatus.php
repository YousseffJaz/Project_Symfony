<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderStatus: int
{
    case WAITING = 0;
    case PARTIAL = 1;
    case PAID = 2;
    case REFUND = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::WAITING => 'En attente',
            self::PARTIAL => 'Partiellement payé',
            self::PAID => 'Payé',
            self::REFUND => 'Remboursé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::WAITING => 'warning',
            self::PARTIAL => 'info',
            self::PAID => 'success',
            self::REFUND => 'danger',
        };
    }
}
