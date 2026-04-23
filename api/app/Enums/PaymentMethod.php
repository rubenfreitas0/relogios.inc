<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MBWAY       = 'mbway';
    case MULTIBANCO  = 'multibanco';
    case CREDIT_CARD = 'credit_card';
    case APPLE_PAY   = 'apple_pay';
    case GOOGLE_PAY  = 'google_pay';

    public function label(): string
    {
        return match($this) {
            self::MBWAY       => 'MB Way',
            self::MULTIBANCO  => 'Multibanco',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::APPLE_PAY   => 'Apple Pay',
            self::GOOGLE_PAY  => 'Google Pay',
        };
    }
}
