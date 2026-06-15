<?php

namespace Database\Seeders;

use App\Models\CartItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Product;

class CartItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            CartItem::factory()->count(5)->create();
            return;
        }

        // Give a few random users some cart items
        foreach ($users->take(3) as $user) {
            $userProducts = $products->random(rand(1, 2));
            foreach ($userProducts as $product) {
                CartItem::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
