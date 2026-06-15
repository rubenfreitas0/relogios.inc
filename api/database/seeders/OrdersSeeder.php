<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;



use App\Models\User;
use App\Models\ShippingMethod;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();
        $shippingMethods = ShippingMethod::all();

        if ($users->isEmpty()) {
            Order::factory()->count(5)->create();
            return;
        }

        foreach (range(1, 5) as $i) {
            Order::factory()->create([
                'user_id' => $users->random()->id,
                'shipping_method_id' => $shippingMethods->isNotEmpty() ? $shippingMethods->random()->id : null,
            ]);
        }
    }
}
