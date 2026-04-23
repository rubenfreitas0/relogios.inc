<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'method'  => PaymentMethod::class,
            'status'  => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Se o status mudar para PAID, preenche automaticamente o paid_at
        static::updating(function (Payment $payment) {
            if ($payment->isDirty('status') && $payment->status === PaymentStatus::PAID && !$payment->paid_at) {
                $payment->paid_at = now();
            }
        });
    }

    /* ----- Relationships ----- */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /* ----- Helpers ----- */

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Retorna o valor formatado com o símbolo da moeda.
     */
    public function getFormattedAmountAttribute(): string
    {
        $symbol = $this->currency === 'EUR' ? '€' : $this->currency;
        return number_format($this->amount, 2, ',', '.') . ' ' . $symbol;
    }
}
