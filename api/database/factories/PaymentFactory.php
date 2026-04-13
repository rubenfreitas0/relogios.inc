<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;


/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(['mbway', 'multibanco', 'credit_card', 'apple_pay', 'google_pay']),
            'transaction_id' => fake()->uuid(),
            'amount' => fake()->numberBetween(100, 10000),
            'currency' => 'EUR',
            'status' => fake()->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'paid_at' => now(),
        ];
    }
}
