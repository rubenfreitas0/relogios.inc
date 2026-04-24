<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'shipping_firstname',
        'shipping_lastname',
        'shipping_phone',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_postal_code',
        'shipping_country',
        'shipping_method_id',
        'shipping_carrier',
        'estimated_days',
        'weight',
        'nif',
        'subtotal',
        'shipping_cost',
        'total',
        'tracking_number',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status'         => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal'       => 'decimal:2',
            'shipping_cost'  => 'decimal:2',
            'total'          => 'decimal:2',
            'weight'         => 'decimal:3',
            'paid_at'        => 'datetime',
        ];
    }

    /* ----- Relationships ----- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /* ----- Scopes ----- */

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /* ----- Helpers ----- */

    public static function generateOrderNumber(): string
    {
        $maxAttempts = 5;

        for ($i = 0; $i < $maxAttempts; $i++) {
            // Componente temporal (últimos 4 dígitos do timestamp) + random para minimizar colisões
            $timePart = substr((string) now()->getTimestampMs(), -4);
            $number = 'RL-' . $timePart . strtoupper(Str::random(6));

            if (! static::where('order_number', $number)->exists()) {
                return $number;
            }
        }

        // Fallback extremo — praticamente impossível de atingir
        throw new \RuntimeException('Não foi possível gerar um número de encomenda único.');
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [OrderStatus::PENDING, OrderStatus::PROCESSING]);
    }

    public function getTrackingUrl(): ?string
    {
        if (!$this->tracking_number || !$this->shipping_carrier) {
            return null;
        }

        return match (strtolower($this->shipping_carrier)) {
            'ctt' => "https://www.ctt.pt/feapl_2/app/open/objectSearch/objectSearch.jspx?objects={$this->tracking_number}",
            'dhl' => "https://www.dhl.com/pt-pt/home/tracking/tracking-express.html?submit=1&tracking-id={$this->tracking_number}",
            default => null,
        };
    }
}
