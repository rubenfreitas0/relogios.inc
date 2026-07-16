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
            self::PENDING    => 'A Aguardar Confirmação',
            self::PROCESSING => 'Em Processamento',
            self::SHIPPED    => 'Enviado',
            self::DELIVERED  => 'Entregue',
            self::CANCELLED  => 'Cancelado',
            self::REFUNDED   => 'Reembolsado',
        };
    }

    /**
     * Estados para os quais este estado pode avançar (máquina de estados
     * só-para-a-frente: não permite reverter nem saltar fases).
     *
     * @return array<int, OrderStatus>
     */
    public function allowedNext(): array
    {
        return match($this) {
            self::PENDING    => [self::PROCESSING, self::CANCELLED],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED    => [self::DELIVERED],
            self::DELIVERED  => [self::REFUNDED],
            self::CANCELLED  => [self::REFUNDED],
            self::REFUNDED   => [],
        };
    }

    /**
     * Indica se a transição deste estado para $to é válida.
     * Manter o mesmo estado é sempre permitido (não é alteração).
     */
    public function canTransitionTo(OrderStatus $to): bool
    {
        if ($this === $to) {
            return true;
        }

        return in_array($to, $this->allowedNext(), true);
    }
}
