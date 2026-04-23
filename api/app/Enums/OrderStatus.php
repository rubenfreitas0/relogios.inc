<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED    = 'shipped';
    case DELIVERED  = 'delivered';
    case CANCELLED  = 'cancelled';
    case REFUNDED   = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING    => 'Aguardando Confirmação',
            self::PROCESSING => 'Em Processamento',
            self::SHIPPED    => 'Enviado',
            self::DELIVERED  => 'Entregue',
            self::CANCELLED  => 'Cancelado',
            self::REFUNDED   => 'Reembolsado',
        };
    }
}
