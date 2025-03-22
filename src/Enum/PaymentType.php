<?php

namespace App\Enum;

enum PaymentType: int
{
    case ONLINE = 0;
    case LOCAL = 1;

    public function getLabel(): string
    {
        return match($this) {
            self::ONLINE => 'En ligne',
            self::LOCAL => 'Sur place',
        };
    }
} 