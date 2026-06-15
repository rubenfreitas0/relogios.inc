<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;


use App\Models\Order;

class PaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();

        if ($orders->isEmpty()) {
            Payment::factory()->count(5)->create();
            return;
        }

        foreach ($orders as $order) {
            Payment::factory()->create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'status' => $order->payment_status,
                'paid_at' => $order->paid_at,
            ]);
        }
    }
}
